<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index()
    {
        return DB::table('m_rewards')
            ->select('id','name','description','points_cost','stock','is_active')
            ->where('is_active', true)
            ->orderBy('points_cost')
            ->get();
    }

    public function myRedemptions(Request $r)
    {
        return DB::table('detail_redemptions as dr')
            ->join('m_rewards as rw','rw.id','=','dr.reward_id')
            ->select('dr.id','rw.name','dr.points_spent','dr.status','dr.created_at','dr.approved_at')
            ->where('dr.user_id', $r->user()->id)
            ->orderByDesc('dr.created_at')
            ->get();
    }

    public function redeem($rewardId, Request $r)
    {
        $userId = $r->user()->id;

        return DB::transaction(function () use ($userId, $rewardId) {
            // lock reward row
            $reward = DB::table('m_rewards')->lockForUpdate()->where('id',$rewardId)->first();
            if (!$reward || !$reward->is_active) {
                return response()->json(['error'=>'Reward not available'], 404);
            }
            if ($reward->stock <= 0) {
                return response()->json(['error'=>'Out of stock'], 400);
            }

            // hitung saldo poin user (pakai view kalau ada; ini versi inline)
            $balance = (int) DB::table('detail_points_ledger')
                ->where('user_id', $userId)
                ->sum('points');

            if ($balance < $reward->points_cost) {
                return response()->json(['error'=>'Insufficient points'], 400);
            }

            // kurangi stok
            DB::table('m_rewards')
                ->where('id',$rewardId)
                ->update(['stock' => $reward->stock - 1, 'updated_at'=>now()]);

            // buat redemption
            $redemptionId = DB::table('detail_redemptions')->insertGetId([
                'user_id'      => $userId,
                'reward_id'    => $rewardId,
                'points_spent' => $reward->points_cost,
                'status'       => 'requested',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // catat minus poin
            DB::table('detail_points_ledger')->insert([
                'user_id'     => $userId,
                'source_type' => 'redemption',
                'source_id'   => $redemptionId,
                'points'      => -$reward->points_cost,
                'description' => 'Redeem: '.$reward->name,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['ok'=>true, 'redemption_id'=>$redemptionId], 201);
        });
    }
}
