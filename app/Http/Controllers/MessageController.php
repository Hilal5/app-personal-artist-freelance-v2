<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index()
    {
        if (!session('user_id')) {
            return redirect()->route('artist.profile')
                ->with('login_error', 'Silakan login terlebih dahulu.');
        }

        $userId   = session('user_id');
        $isArtist = session('user_role') === 'artist';

        if ($isArtist) {
            $contacts = DB::table('users')
                ->where('role', 'client')
                ->get()
                ->map(function ($user) use ($userId) {
                    $last = DB::table('messages')
                        ->where(function ($q) use ($userId, $user) {
                            $q->where('sender_id', $userId)
                              ->where('receiver_id', $user->id)
                              ->where('deleted_by_sender', false);
                        })
                        ->orWhere(function ($q) use ($userId, $user) {
                            $q->where('sender_id', $user->id)
                              ->where('receiver_id', $userId)
                              ->where('deleted_by_receiver', false);
                        })
                        ->orderByDesc('created_at')
                        ->first();

                    $unread = DB::table('messages')
                        ->where('sender_id', $user->id)
                        ->where('receiver_id', $userId)
                        ->where('is_read', false)
                        ->where('deleted_by_receiver', false)
                        ->count();

                    return [
                        'id'         => $user->id,
                        'name'       => $user->name,
                        'last_msg'   => $last?->message ?? 'Belum ada pesan',
                        'last_time'  => $last?->created_at ?? null,
                        'unread'     => $unread,
                        'has_file'   => $last && $last->file_path ? true : false,
                        'is_blocked' => $user->is_blocked,
                    ];
                });

            $activeContactId = request('contact');
            $activeContact   = $activeContactId
                ? DB::table('users')->where('id', $activeContactId)->first()
                : null;

            $messages = collect();

            if ($activeContact) {
                DB::table('messages')
                    ->where('sender_id', $activeContact->id)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);

                $messages = DB::table('messages')
                ->where('deleted_by_artist', false)
                    ->where(function ($q) use ($userId, $activeContact) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $activeContact->id)
                          ->where('deleted_by_sender', false);
                    })
    ->orWhere(function ($q) use ($userId, $activeContact) {
        $q->where('deleted_by_artist', false) // ← TAMBAH INI juga di orWhere
          ->where('sender_id', $activeContact->id)
          ->where('receiver_id', $userId)
          ->where('deleted_by_receiver', false);
    })
    ->orderBy('created_at')
    ->get();
            }

            $artist = null;

            return view('chat.index', compact(
                'contacts', 'activeContact', 'messages', 'isArtist', 'artist'
            ));
        }

        // CLIENT
        $artist = DB::table('users')->where('role', 'artist')->first();

        DB::table('messages')
            ->where('sender_id', $artist->id)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

$messages = DB::table('messages')
    ->where('deleted_by_artist', false) // ← TAMBAH INI
    ->where(function ($q) use ($userId, $artist) {
        $q->where('sender_id', $userId)
          ->where('receiver_id', $artist->id)
          ->where('deleted_by_sender', false);
    })
    ->orWhere(function ($q) use ($userId, $artist) {
        $q->where('deleted_by_artist', false) // ← TAMBAH INI juga di orWhere
          ->where('sender_id', $artist->id)
          ->where('receiver_id', $userId)
          ->where('deleted_by_receiver', false);
    })
    ->orderBy('created_at')
    ->get();

        $contacts      = collect();
        $activeContact = $artist;

        return view('chat.index', compact(
            'contacts', 'activeContact', 'messages', 'isArtist', 'artist'
        ));
    }

    public function send(Request $request)
    {
        if (!session('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Cek apakah client diblokir
        $userId   = session('user_id');
        $isArtist = session('user_role') === 'artist';

        if (!$isArtist) {
            $artist  = DB::table('users')->where('role', 'artist')->first();
            $blocked = DB::table('users')
                ->where('id', $userId)
                ->where('is_blocked', true)
                ->exists();
            if ($blocked) {
                return response()->json(['error' => 'Kamu diblokir oleh artist.'], 403);
            }
        }

        // GANTI bagian validasi + file handling ini
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'nullable|string|max:2000',
            'file'        => 'nullable|file|max:51200',  // hapus mimes — validasi manual di bawah
        ]);

        $filePath = null;
        $fileType = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file      = $request->file('file');
            $fileName  = $file->getClientOriginalName();
            $ext       = strtolower($file->getClientOriginalExtension());

            // Allowed extensions
            $allowed = ['jpg','jpeg','png','gif','webp','mp4','mov','avi','pdf','zip','psd','ai','sketch','fig'];
            if (!in_array($ext, $allowed)) {
                return response()->json(['error' => 'Tipe file tidak diizinkan'], 422);
            }

            // Force file_type yang benar berdasarkan ekstensi
            $mimeMap = [
                'jpg'    => 'image/jpeg',
                'jpeg'   => 'image/jpeg',
                'png'    => 'image/png',
                'gif'    => 'image/gif',
                'webp'   => 'image/webp',
                'mp4'    => 'video/mp4',
                'mov'    => 'video/quicktime',
                'avi'    => 'video/x-msvideo',
                'pdf'    => 'application/pdf',
                'zip'    => 'application/zip',
                'psd'    => 'application/psd',
                'ai'     => 'application/ai',
                'sketch' => 'application/sketch',
                'fig'    => 'application/fig',
            ];

            $fileType = $mimeMap[$ext] ?? $file->getMimeType();
            $filePath = $file->store('chat-files', 'public');
        }

        if (!$request->message && !$filePath) {
            return response()->json(['error' => 'Pesan atau file wajib diisi'], 422);
        }

        $msgId = DB::table('messages')->insertGetId([
            'sender_id'   => $userId,
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
            'file_path'   => $filePath,
            'file_type'   => $fileType,
            'file_name'   => $fileName,
            'is_read'     => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $message = DB::table('messages')->where('id', $msgId)->first();

        return response()->json([
            'id'        => $message->id,
            'message'   => $message->message,
            'file_path' => $message->file_path ? Storage::url($message->file_path) : null,
            'file_type' => $message->file_type,
            'file_name' => $message->file_name,
            'mine'      => true,
            'time'      => \Carbon\Carbon::parse($message->created_at)->format('H:i'),
            'sender_id' => $message->sender_id,
        ]);
    }

    public function fetch(Request $request)
{
    if (!session('user_id')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $userId    = session('user_id');
    $contactId = $request->contact_id;
    $lastId    = $request->last_id ?? 0;

    // ✅ Ambil SEMUA pesan dalam conversation (dua arah) untuk deteksi delete
    $messages = DB::table('messages')
        ->where('deleted_by_artist', false)
        ->where(function ($q) use ($userId, $contactId) {
            $q->where(function ($q2) use ($userId, $contactId) {
                $q2->where('sender_id', $userId)
                   ->where('receiver_id', $contactId)
                   ->where('deleted_by_sender', false);
            })->orWhere(function ($q2) use ($userId, $contactId) {
                $q2->where('sender_id', $contactId)
                   ->where('receiver_id', $userId)
                   ->where('deleted_by_receiver', false);
            });
        })
        ->orderBy('created_at')
        ->get()
        ->map(function ($m) use ($userId) {
            return [
                'id'        => $m->id,
                'message'   => $m->message,
                'file_path' => $m->file_path ? Storage::url($m->file_path) : null,
                'file_type' => $m->file_type,
                'file_name' => $m->file_name,
                'mine'      => $m->sender_id == $userId,
                'time'      => \Carbon\Carbon::parse($m->created_at)->format('H:i'),
                'sender_id' => $m->sender_id,
            ];
        });

    DB::table('messages')
        ->where('sender_id', $contactId)
        ->where('receiver_id', $userId)
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json($messages);
}

public function deleteMessage(Request $request)
{
    if (!session('user_id')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $userId   = session('user_id');
    $isArtist = session('user_role') === 'artist';
    $message  = DB::table('messages')->where('id', $request->message_id)->first();

    if (!$message) {
        return response()->json(['error' => 'Pesan tidak ditemukan'], 404);
    }

    // ✅ Artist bisa hapus semua pesan dalam conversation
    // ✅ User hanya bisa hapus pesan miliknya sendiri
    if ($isArtist) {
        if ($message->sender_id != $userId && $message->receiver_id != $userId) {
            return response()->json(['error' => 'Tidak diizinkan'], 403);
        }
    } else {
        if ($message->sender_id != $userId) {
            return response()->json(['error' => 'Kamu hanya bisa hapus pesanmu sendiri'], 403);
        }
    }

    DB::table('messages')->where('id', $request->message_id)->delete();

    return response()->json(['success' => true]);
}

    public function blockUser(Request $request)
    {
        if (!session('user_id') || session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = DB::table('users')->where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $newStatus = !$user->is_blocked;
        DB::table('users')
            ->where('id', $request->user_id)
            ->update(['is_blocked' => $newStatus]);

        return response()->json([
            'success'    => true,
            'is_blocked' => $newStatus,
            'message'    => $newStatus ? 'User berhasil diblokir' : 'User berhasil di-unblokir',
        ]);
    }
}