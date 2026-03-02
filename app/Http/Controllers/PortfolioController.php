<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    // Halaman publik
    public function index()
    {
        $query = DB::table('portfolios')
            ->where('status', 'published')
            ->orderByDesc('created_at');

        // Filter kategori
        if (request('category')) {
            $query->where('category', request('category'));
        }

        // Filter software
        if (request('software')) {
            $query->where('software', request('software'));
        }

        // Search
        if (request('search')) {
            $query->where('title', 'like', '%' . request('search') . '%');
        }

        // Sort
        if (request('sort') === 'oldest') {
            $query->reorder('created_at', 'asc');
        } elseif (request('sort') === 'popular') {
            $query->reorder('views', 'desc');
        }

        $portfolios = $query->get()->map(function ($p) {
            $p->cover = DB::table('portfolio_media')
                ->where('portfolio_id', $p->id)
                ->where('is_cover', true)
                ->first();

            if (!$p->cover) {
                $p->cover = DB::table('portfolio_media')
                    ->where('portfolio_id', $p->id)
                    ->first();
            }

            $p->tags = DB::table('portfolio_tags')
                ->where('portfolio_id', $p->id)
                ->pluck('tag');

            $p->media = DB::table('portfolio_media')
                ->where('portfolio_id', $p->id)
                ->orderBy('order')
                ->get();

            return $p;
        });

        $categories = DB::table('portfolios')
            ->where('status', 'published')
            ->distinct()->pluck('category');

        $softwares = DB::table('portfolios')
            ->where('status', 'published')
            ->whereNotNull('software')
            ->distinct()->pluck('software');

        return view('portfolio.index', compact('portfolios', 'categories', 'softwares'));
    }

    // Track views via AJAX
    public function view($id)
    {
        DB::table('portfolios')->where('id', $id)->increment('views');
        return response()->json(['success' => true]);
    }

    // ===== ARTIST: Manage =====
    public function manage()
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }

        $portfolios = DB::table('portfolios')
            ->where('user_id', session('user_id'))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                $p->cover = DB::table('portfolio_media')
                    ->where('portfolio_id', $p->id)
                    ->where('is_cover', true)
                    ->first()
                    ?? DB::table('portfolio_media')
                    ->where('portfolio_id', $p->id)
                    ->first();

                $p->tags = DB::table('portfolio_tags')
                    ->where('portfolio_id', $p->id)
                    ->pluck('tag');

                return $p;
            });

        return view('portfolio.manage', compact('portfolios'));
    }

    public function create()
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');
        return view('portfolio.create');
    }

    public function store(Request $request)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'required|string|max:100',
            'software'     => 'nullable|string|max:100',
            'client_name'  => 'nullable|string|max:100',
            'created_date' => 'nullable|date',
            'status'       => 'required|in:published,draft',
            'tags'         => 'nullable|string',
            'media'        => 'nullable|array|max:20',
            'media.*'      => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov',
            'cover_index'  => 'nullable|integer',
        ]);

        $portfolioId = DB::table('portfolios')->insertGetId([
            'user_id'      => session('user_id'),
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'software'     => $request->software,
            'client_name'  => $request->client_name,
            'created_date' => $request->created_date,
            'status'       => $request->status,
            'views'        => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Tags
        if ($request->tags) {
            $tags = array_filter(array_map('trim', explode(',', $request->tags)));
            foreach ($tags as $tag) {
                DB::table('portfolio_tags')->insert([
                    'portfolio_id' => $portfolioId,
                    'tag'          => strtolower($tag),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // Media upload
        if ($request->hasFile('media')) {
            $coverIndex = (int) ($request->cover_index ?? 0);
            foreach ($request->file('media') as $i => $file) {
                $path = $file->store('portfolio-media', 'public');
                DB::table('portfolio_media')->insert([
                    'portfolio_id' => $portfolioId,
                    'file_path'    => $path,
                    'file_type'    => str_contains($file->getMimeType(), 'video') ? 'video' : 'image',
                    'is_cover'     => $i === $coverIndex,
                    'order'        => $i,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        return redirect()->route('portfolio.manage')
            ->with('success', 'Portfolio berhasil ditambahkan!');
    }

    public function edit($id)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $portfolio = DB::table('portfolios')
            ->where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$portfolio) abort(404);

        $portfolio->media = DB::table('portfolio_media')
            ->where('portfolio_id', $id)->orderBy('order')->get();

        $portfolio->tags = DB::table('portfolio_tags')
            ->where('portfolio_id', $id)->pluck('tag');

        return view('portfolio.edit', compact('portfolio'));
    }

    public function update(Request $request, $id)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $portfolio = DB::table('portfolios')
            ->where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$portfolio) abort(404);

        $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category'     => 'required|string|max:100',
            'software'     => 'nullable|string|max:100',
            'client_name'  => 'nullable|string|max:100',
            'created_date' => 'nullable|date',
            'status'       => 'required|in:published,draft',
            'tags'         => 'nullable|string',
            'media'        => 'nullable|array|max:20',
            'media.*'      => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov',
        ]);

        DB::table('portfolios')->where('id', $id)->update([
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'software'     => $request->software,
            'client_name'  => $request->client_name,
            'created_date' => $request->created_date,
            'status'       => $request->status,
            'updated_at'   => now(),
        ]);

        // Update tags
        DB::table('portfolio_tags')->where('portfolio_id', $id)->delete();
        if ($request->tags) {
            $tags = array_filter(array_map('trim', explode(',', $request->tags)));
            foreach ($tags as $tag) {
                DB::table('portfolio_tags')->insert([
                    'portfolio_id' => $id,
                    'tag'          => strtolower($tag),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // Upload media baru
        if ($request->hasFile('media')) {
            $lastOrder = DB::table('portfolio_media')
                ->where('portfolio_id', $id)->max('order') ?? -1;
            foreach ($request->file('media') as $i => $file) {
                $path = $file->store('portfolio-media', 'public');
                DB::table('portfolio_media')->insert([
                    'portfolio_id' => $id,
                    'file_path'    => $path,
                    'file_type'    => str_contains($file->getMimeType(), 'video') ? 'video' : 'image',
                    'is_cover'     => false,
                    'order'        => $lastOrder + $i + 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // Hapus media
        if ($request->delete_media) {
            foreach ($request->delete_media as $mediaId) {
                $media = DB::table('portfolio_media')->where('id', $mediaId)->first();
                if ($media) {
                    Storage::disk('public')->delete($media->file_path);
                    DB::table('portfolio_media')->where('id', $mediaId)->delete();
                }
            }
        }

        // Set cover
        if ($request->cover_media_id) {
            DB::table('portfolio_media')->where('portfolio_id', $id)->update(['is_cover' => false]);
            DB::table('portfolio_media')->where('id', $request->cover_media_id)->update(['is_cover' => true]);
        }

        return redirect()->route('portfolio.manage')
            ->with('success', 'Portfolio berhasil diupdate!');
    }

    public function destroy($id)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $media = DB::table('portfolio_media')->where('portfolio_id', $id)->get();
        foreach ($media as $m) {
            Storage::disk('public')->delete($m->file_path);
        }

        DB::table('portfolio_media')->where('portfolio_id', $id)->delete();
        DB::table('portfolio_tags')->where('portfolio_id', $id)->delete();
        DB::table('portfolios')->where('id', $id)->delete();

        return redirect()->route('portfolio.manage')
            ->with('success', 'Portfolio berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $portfolio = DB::table('portfolios')->where('id', $id)->first();
        $newStatus = $portfolio->status === 'published' ? 'draft' : 'published';

        DB::table('portfolios')->where('id', $id)->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    // Tambahkan method ini setelah method index()
public function show($id)
{
    $portfolio = DB::table('portfolios')
        ->where('id', $id)
        ->where('status', 'published')
        ->first();

    if (!$portfolio) abort(404);

    // Track view
    DB::table('portfolios')->where('id', $id)->increment('views');

    $portfolio->media = DB::table('portfolio_media')
        ->where('portfolio_id', $id)
        ->orderBy('order')
        ->get();

    $portfolio->tags = DB::table('portfolio_tags')
        ->where('portfolio_id', $id)
        ->pluck('tag');

    // Related portfolios (kategori sama, exclude current)
    $related = DB::table('portfolios')
        ->where('status', 'published')
        ->where('category', $portfolio->category)
        ->where('id', '!=', $id)
        ->limit(4)
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

    return view('portfolio.show', compact('portfolio', 'related'));
}
}