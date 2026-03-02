<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    // Halaman publik
    public function index()
    {
        $faqs = DB::table('faqs')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        $categories = $faqs->keys();

        return view('faq.index', compact('faqs', 'categories'));
    }

    // Artist: manage
    public function manage()
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('faq.index');
        }

        $faqs = DB::table('faqs')
            ->orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category');

        $categories = DB::table('faqs')->distinct()->pluck('category');

        return view('faq.manage', compact('faqs', 'categories'));
    }

    // Artist: store
    public function store(Request $request)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'category' => 'required|string|max:100',
            'question' => 'required|string|max:300',
            'answer'   => 'required|string',
        ]);

        $maxOrder = DB::table('faqs')
            ->where('category', $request->category)
            ->max('order') ?? 0;

        $id = DB::table('faqs')->insertGetId([
            'user_id'    => session('user_id'),
            'category'   => $request->category,
            'question'   => $request->question,
            'answer'     => $request->answer,
            'order'      => $maxOrder + 1,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    // Artist: update
    public function update(Request $request, $id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'category' => 'required|string|max:100',
            'question' => 'required|string|max:300',
            'answer'   => 'required|string',
        ]);

        DB::table('faqs')->where('id', $id)->update([
            'category'   => $request->category,
            'question'   => $request->question,
            'answer'     => $request->answer,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // Artist: toggle active
    public function toggle($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $faq = DB::table('faqs')->where('id', $id)->first();
        DB::table('faqs')->where('id', $id)->update([
            'is_active'  => !$faq->is_active,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'is_active' => !$faq->is_active]);
    }

    // Artist: delete
    public function destroy($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('faqs')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}