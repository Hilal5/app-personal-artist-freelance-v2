<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // Ambil notifikasi untuk navbar (AJAX polling)
    public function index()
    {
        if (!session('user_id')) {
            return response()->json(['notifications' => [], 'unread' => 0]);
        }

        $notifications = DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function ($n) {
                $n->time_ago = \Carbon\Carbon::parse($n->created_at)->diffForHumans();
                return $n;
            });

        $unread = DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->where('is_read', false)
            ->count();

        return response()->json(compact('notifications', 'unread'));
    }

    // Tandai semua sudah dibaca
    public function readAll()
    {
        if (!session('user_id')) return response()->json(['success' => false]);

        DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Tandai satu sudah dibaca + redirect
    public function read($id)
    {
        $notif = DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if ($notif) {
            DB::table('notifications')->where('id', $id)->update(['is_read' => true]);
            return redirect($notif->url ?? '/');
        }

        return redirect('/');
    }
}