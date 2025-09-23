<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // GET /api/users
    public function index(Request $r)
    {
        $q = User::query()
            ->select('id','name','full_name','email','status','role',
                     'address','city','province','postal_code','created_at');

        // filter optional
        if ($r->filled('q')) {
            $s = trim($r->q);
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")
                  ->orWhere('full_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }
        if ($r->filled('status')) {
            $q->where('status', $r->status); // pending|active|suspended
        }
        if ($r->filled('role')) {
            $q->where('role', $r->role);     // admin|member
        }

        $q->orderByDesc('created_at');

        // paginate by default
        $per = (string) $r->get('per_page', '20');
        if ($per === '0' || strtolower($per) === 'all') {
            return $q->get();
        }
        return $q->paginate((int) $per);
    }
}
