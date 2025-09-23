<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\RewardCategory;
use App\Models\Redemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class RewardController extends Controller
{
    /** PUBLIC: list kategori */
    public function categories()
    {
        return RewardCategory::select('id', 'name')->orderBy('name')->get();
    }

    /**
     * PUBLIC: list rewards
     * Query:
     * - category_id=...
     * - min_points=...
     * - max_points=...
     * - sort=points_asc|points_desc
     * - per_page=... (default 20; 0/all = non-paginated)
     */
    public function index(Request $r)
    {
        $q = Reward::query()->with('category');

        if ($r->filled('category_id')) {
            $q->where('category_id', (int) $r->category_id);
        }
        if ($r->filled('min_points')) {
            $q->where('points_cost', '>=', (int) $r->min_points);
        }
        if ($r->filled('max_points')) {
            $q->where('points_cost', '<=', (int) $r->max_points);
        }

        $sort = $r->get('sort');
        if ($sort === 'points_asc')
            $q->orderBy('points_cost', 'asc');
        elseif ($sort === 'points_desc')
            $q->orderBy('points_cost', 'desc');
        else
            $q->orderByDesc('id');

        $perPage = (string) $r->get('per_page', '20');
        if ($perPage === '0' || strcasecmp($perPage, 'all') === 0) {
            return $q->get();
        }
        return $q->paginate((int) $perPage);
    }

    /** PUBLIC: detail reward */
    public function show($id)
    {
        return Reward::with('category')->findOrFail($id);
    }

    /**
     * ADMIN: create reward (multipart/form-data)
     * Fields:
     * - title (string, required)
     * - description (string, optional)
     * - points_cost (int, required)
     * - stock (int|null)
     * - category_id (int|null, exists:reward_categories,id)
     * - image (file, optional: jpg/png/webp <= 2MB)
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'points_cost' => ['required', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:reward_categories,id'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // simpan file ke storage
        $path = null;
        if ($r->hasFile('image')) {
            $path = $r->file('image')->store('rewards', 'public'); // storage/app/public/rewards/...
        }

        $reward = Reward::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'points_cost' => $data['points_cost'],
            'stock' => $data['stock'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json($reward->fresh('category'), 201);
    }

    /**
     * ADMIN: update reward (multipart/form-data)
     * Boleh ganti foto: kirim field "image"
     */
    public function update($id, Request $r)
    {
        $reward = Reward::findOrFail($id);

        $data = $r->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'points_cost' => ['sometimes', 'integer', 'min:0'],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:reward_categories,id'],
            'image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // handle image replace
        if ($r->hasFile('image')) {
            // hapus lama jika ada
            if ($reward->image_path && Storage::disk('public')->exists($reward->image_path)) {
                Storage::disk('public')->delete($reward->image_path);
            }
            $reward->image_path = $r->file('image')->store('rewards', 'public');
        }

        $reward->fill($data)->save();

        return $reward->fresh('category');
    }

    /** ADMIN: delete reward (hapus file juga) */
    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);

        if ($reward->image_path && Storage::disk('public')->exists($reward->image_path)) {
            Storage::disk('public')->delete($reward->image_path);
        }
        $reward->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * USER (isActive): redeem reward
     * - cek stok (jika di-set)
     * - cek poin user cukup
     * - kurangi stok
     * - catat ke redemptions + ledger poin
     *
     * NOTE: Sesuaikan mekanisme pengurangan poin user (tergantung struktur points kamu).
     */
    public function redeem($id, Request $r)
    {
        $user = $r->user();

        return DB::transaction(function () use ($id, $user) {
            // Kunci row reward DI DALAM transaksi
            $reward = Reward::lockForUpdate()->findOrFail($id);

            // Cek stok
            if (!is_null($reward->stock) && $reward->stock <= 0) {
                return response()->json(['message' => 'Stok habis'], 400);
            }

            // Hitung total poin user (positif = dapat, negatif = spend)
            $totalPoints = (int) DB::table('detail_points_ledger')
                ->where('user_id', $user->id)
                ->sum('points');

            if ($totalPoints < (int) $reward->points_cost) {
                return response()->json(['message' => 'Poin tidak cukup'], 400);
            }

            // Buat redemption + kode voucher
            $code = Str::upper(Str::random(10));

            /** @var \App\Models\Redemption $red */
            $red = Redemption::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'status' => 'issued',
                'points_spent' => (int) $reward->points_cost,
                'voucher_code' => $code,
                'expires_at' => now()->addDays(30),
            ]);

            // Kurangi stok jika ada
            if (!is_null($reward->stock)) {
                $reward->decrement('stock');
            }

            // Catat pengurangan poin
            DB::table('detail_points_ledger')->insert([
                'user_id' => $user->id,
                'source_type' => 'reward',        // pastikan tipe kolom mengizinkan 'reward'
                'source_id' => $reward->id,
                'points' => -((int) $reward->points_cost),
                'description' => 'Redeem reward: ' . $reward->title,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'ok' => true,
                'voucher_code' => $code,
                'redemption_id' => $red->id,
            ], 201);
        });
    }
    public function myRedemptions(Request $r)
    {
        $rows = Redemption::query()
            ->where('user_id', $r->user()->id)
            ->with(['reward:id,title,image_path,points_cost,description'])
            ->select('id', 'user_id', 'reward_id', 'status', 'voucher_code', 'expires_at', 'created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($x) {
                if ($x->reward) {
                    $img = $x->reward->image_path ? \Storage::url($x->reward->image_path) : null;
                    $x->reward->setAttribute('image_url', $img); // aman untuk PHP 8.2
                }
                return $x;
            });

        return response()->json($rows);
    }


}