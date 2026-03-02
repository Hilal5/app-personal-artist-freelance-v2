<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    public function index()
    {
        $artist = DB::table('users')->where('role', 'artist')->first();

        if (!$artist) {
            return view('artist.profile', ['artist' => null]);
        }

        // Stats
        $stats = [
            'portfolio' => DB::table('portfolios')
                ->where('user_id', $artist->id)
                ->where('status', 'published')->count(),
            'orders'    => DB::table('orders')
                ->where('artist_id', $artist->id)
                ->where('status', 'completed')->count(),
            'reviews'   => DB::table('reviews')
                ->where('status', 'approved')->count(),
            'rating'    => DB::table('reviews')
                ->where('status', 'approved')->avg('rating') ?? 0,
            'views'     => DB::table('portfolios')
                ->where('user_id', $artist->id)->sum('views'),
        ];

        // Portfolio terbaru (6)
        $portfolios = DB::table('portfolios')
            ->where('user_id', $artist->id)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function ($p) {
                $p->cover = DB::table('portfolio_media')
                    ->where('portfolio_id', $p->id)
                    ->where('is_cover', true)
                    ->first()
                    ?? DB::table('portfolio_media')
                    ->where('portfolio_id', $p->id)
                    ->first();
                return $p;
            });

        // Review terbaru (3)
        $reviews = DB::table('reviews')
            ->join('users as clients', 'reviews.client_id', '=', 'clients.id')
            ->join('commissions', 'reviews.commission_id', '=', 'commissions.id')
            ->select('reviews.*', 'clients.name as client_name', 'commissions.title as commission_title')
            ->where('reviews.status', 'approved')
            ->orderByDesc('reviews.created_at')
            ->limit(3)
            ->get()
            ->map(function ($r) {
                $r->quick_tags = json_decode($r->quick_tags ?? '[]', true);
                return $r;
            });

        // Commission terbuka
        $commissions = DB::table('commissions')
            ->where('user_id', $artist->id)
            ->where('status', 'open')
            ->limit(3)
            ->get();

        return view('artist.profile', compact('artist', 'stats', 'portfolios', 'reviews', 'commissions'));
    }

    public function update(Request $request)
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }

        $request->validate([
            'name'              => 'required|string|max:100',
            'username'          => 'nullable|string|max:50|unique:users,username,' . session('user_id'),
            'bio'               => 'nullable|string|max:500',
            'location'          => 'nullable|string|max:100',
            'language'          => 'nullable|string|max:50',
            'instagram'         => 'nullable|string|max:100',
            'twitter'           => 'nullable|string|max:100',
            'tiktok'            => 'nullable|string|max:100',
            'website'           => 'nullable|url|max:200',
            'commission_status' => 'required|in:open,closed',
            'avatar'            => 'nullable|file|max:5120|mimes:jpg,jpeg,png,webp',
        ]);

        $data = [
            'name'              => $request->name,
            'username'          => $request->username,
            'bio'               => $request->bio,
            'location'          => $request->location,
            'language'          => $request->language,
            'instagram'         => $request->instagram,
            'twitter'           => $request->twitter,
            'tiktok'            => $request->tiktok,
            'website'           => $request->website,
            'commission_status' => $request->commission_status,
            'updated_at'        => now(),
        ];

        // Upload avatar
        if ($request->hasFile('avatar')) {
            $artist = DB::table('users')->where('id', session('user_id'))->first();
            if ($artist->avatar) {
                Storage::disk('public')->delete($artist->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        DB::table('users')->where('id', session('user_id'))->update($data);

        // Update session name
        session(['user_name' => $request->name]);

        return redirect()->route('artist.profile')
            ->with('success', 'Profil berhasil diupdate!');
    }
}