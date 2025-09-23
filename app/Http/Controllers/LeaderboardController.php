<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    // default: monthly leaderboard (bulan berjalan)
    public function index()
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $rows = DB::table('detail_points_ledger as l')
            ->join('users as u','u.id','=','l.user_id')
            ->select('u.id','u.name','u.full_name', DB::raw('SUM(l.points) as points'))
            ->whereBetween('l.created_at', [$start, $end])
            ->groupBy('u.id','u.name','u.full_name')
            ->orderByDesc('points')
            ->limit(10)
            ->get();

        return $rows;
    }

    // weekly leaderboard (minggu berjalan)
    public function weekly()
    {
        $start = now()->startOfWeek();
        $end   = now()->endOfWeek();

        $rows = DB::table('detail_points_ledger as l')
            ->join('users as u','u.id','=','l.user_id')
            ->select('u.id','u.name','u.full_name', DB::raw('SUM(l.points) as points'))
            ->whereBetween('l.created_at', [$start, $end])
            ->groupBy('u.id','u.name','u.full_name')
            ->orderByDesc('points')
            ->limit(10)
            ->get();

        return $rows;
    }

    // custom range: /api/leaderboard/custom?start=2025-09-01&end=2025-09-22
    public function custom(Request $r)
    {
        $r->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $rows = DB::table('detail_points_ledger as l')
            ->join('users as u','u.id','=','l.user_id')
            ->select('u.id','u.name','u.full_name', DB::raw('SUM(l.points) as points'))
            ->whereBetween('l.created_at', [$r->start, $r->end])
            ->groupBy('u.id','u.name','u.full_name')
            ->orderByDesc('points')
            ->limit(50)
            ->get();

        return $rows;
    }
}
    