<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RememberUser
{
    public function handle(Request $request, Closure $next)
    {
        // Kalau belum login tapi ada remember cookie
        if (!session('user_id') && $request->cookie('remember_user')) {
            try {
                $userId = decrypt($request->cookie('remember_user'));
                $user   = DB::table('users')->where('id', $userId)->first();

                if ($user) {
                    session([
                        'user_id'   => $user->id,
                        'user_name' => $user->name,
                        'user_role' => $user->role,
                    ]);
                }
            } catch (\Exception $e) {
                cookie()->queue(cookie()->forget('remember_user'));
            }
        }

        return $next($request);
    }
}