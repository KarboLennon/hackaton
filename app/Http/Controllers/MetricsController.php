<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricsController extends Controller
{
    /**
     * GET /api/metrics/summary?from=YYYY-MM-DD&to=YYYY-MM-DD
     * Mengembalikan:
     * {
     *   "range": { "from": "...", "to": "..." },
     *   "totals": {
     *      "members": 0,
     *      "posts": 0,
     *      "reach": 0,          // pakai SUM(views)
     *      "sales_count": 0,    // dummy 0 kalau belum ada tabel
     *      "sales_amount": 0    // dummy 0 kalau belum ada kolom amount
     *   },
     *   "engagement": {
     *      "likes": 0,
     *      "comments": 0,
     *      "shares": 0,
     *      "er": 0              // dihitung di FE juga gpp, ini opsional
     *   }
     * }
     */
    public function summary(Request $request)
    {
        // baca & validasi tanggal
        $from = $request->query('from');
        $to   = $request->query('to');

        try {
            $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(7)->startOfDay();
            $toDate   = $to   ? Carbon::parse($to)->endOfDay()   : now()->endOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Invalid date format. Use YYYY-MM-DD.',
            ], 422);
        }

        // ===== Total members (users)
        $members = 0;
        try {
            $members = DB::table('users')->count();
        } catch (\Throwable $e) {
            $members = 0;
        }

        // ===== Posts & engagement dari detail_submissions + detail_content_metrics
        // posts = jumlah submission di range tsb (kalau mau harian pakai created_at di submissions)
        $posts = 0;
        try {
            $posts = DB::table('detail_submissions')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->count();
        } catch (\Throwable $e) {
            $posts = 0;
        }

        // content metrics (likes, comments, shares, reach = views)
        $likes = $comments = $shares = $reach = 0;
        try {
            // pastikan tabel & kolom ini memang ada: detail_content_metrics: likes, comments, shares, views
            $row = DB::table('detail_content_metrics')
                ->selectRaw('COALESCE(SUM(likes),0)    as sum_likes,
                             COALESCE(SUM(comments),0) as sum_comments,
                             COALESCE(SUM(shares),0)   as sum_shares,
                             COALESCE(SUM(views),0)    as sum_views')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->first();

            if ($row) {
                $likes    = (int) $row->sum_likes;
                $comments = (int) $row->sum_comments;
                $shares   = (int) $row->sum_shares;
                $reach    = (int) $row->sum_views; // ← reach dari views
            }
        } catch (\Throwable $e) {
            // biarin 0
        }

        // ===== Sales (dummy 0 kalau belum ada tabel/kolom)
        $salesCount  = 0;
        $salesAmount = 0;

        // Contoh kalau kamu nantinya punya tabel orders:
        // try {
        //     $salesCount = DB::table('orders')
        //         ->whereBetween('created_at', [$fromDate, $toDate])
        //         ->where('payment_status', 'paid')
        //         ->count();
        //     $salesAmount = (int) DB::table('orders')
        //         ->whereBetween('created_at', [$fromDate, $toDate])
        //         ->where('payment_status', 'paid')
        //         ->sum('total_price');
        // } catch (\Throwable $e) {
        //     // fallback 0
        // }

        // Engagement rate sederhana (opsional, FE juga itung)
        $interactions = $likes + $comments + $shares;
        $er = ($reach > 0) ? round(($interactions / $reach) * 100, 2) : 0;

        return response()->json([
            'range' => [
                'from' => $fromDate->toDateString(),
                'to'   => $toDate->toDateString(),
            ],
            'totals' => [
                'members'      => $members,
                'posts'        => $posts,
                'reach'        => $reach,
                'sales_count'  => $salesCount,
                'sales_amount' => $salesAmount,
            ],
            'engagement' => [
                'likes'    => $likes,
                'comments' => $comments,
                'shares'   => $shares,
                'er'       => $er,
            ],
        ]);
    }
}
