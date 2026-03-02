<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    // Halaman publik reviews (hanya approved)
    public function index()
    {
        $reviews = DB::table('reviews')
            ->join('users as clients', 'reviews.client_id', '=', 'clients.id')
            ->join('commissions', 'reviews.commission_id', '=', 'commissions.id')
            ->select('reviews.*', 'clients.name as client_name', 'commissions.title as commission_title')
            ->where('reviews.status', 'approved')
            ->orderByDesc('reviews.created_at')
            ->get()
            ->map(function ($r) {
                $r->quick_tags = json_decode($r->quick_tags ?? '[]', true);
                return $r;
            });

        $avgRating = $reviews->avg('rating');
        $total     = $reviews->count();

        $ratingCounts = [];
        for ($i = 5; $i >= 1; $i--) {
            $ratingCounts[$i] = $reviews->where('rating', $i)->count();
        }

        return view('reviews.index', compact('reviews', 'avgRating', 'total', 'ratingCounts'));
    }

    // Artist: manage reviews
    public function manage()
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }

        $status = request('status', 'pending');

        $reviews = DB::table('reviews')
            ->join('users as clients', 'reviews.client_id', '=', 'clients.id')
            ->join('commissions', 'reviews.commission_id', '=', 'commissions.id')
            ->select('reviews.*', 'clients.name as client_name', 'commissions.title as commission_title')
            ->when($status !== 'all', fn($q) => $q->where('reviews.status', $status))
            ->orderByDesc('reviews.created_at')
            ->get()
            ->map(function ($r) {
                $r->quick_tags = json_decode($r->quick_tags ?? '[]', true);
                return $r;
            });

        $counts = [
            'all'      => DB::table('reviews')->count(),
            'pending'  => DB::table('reviews')->where('status', 'pending')->count(),
            'approved' => DB::table('reviews')->where('status', 'approved')->count(),
            'rejected' => DB::table('reviews')->where('status', 'rejected')->count(),
        ];

        return view('reviews.manage', compact('reviews', 'status', 'counts'));
    }

    // Client: form submit review
    public function create($orderId)
    {
        if (!session('user_id')) {
            return redirect()->route('commission.index');
        }

        $order = DB::table('orders')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->select('orders.*', 'commissions.title as commission_title')
            ->where('orders.id', $orderId)
            ->where('orders.client_id', session('user_id'))
            ->where('orders.status', 'completed')
            ->first();

        if (!$order) {
            return redirect()->route('orders.client')
                ->with('error', 'Order tidak ditemukan atau belum selesai.');
        }

        // Cek sudah pernah review
        $existing = DB::table('reviews')->where('order_id', $orderId)->first();
        if ($existing) {
            return redirect()->route('orders.client')
                ->with('error', 'Kamu sudah memberikan review untuk order ini.');
        }

        return view('reviews.create', compact('order'));
    }

    // Client: simpan review
    public function store(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('commission.index');
        }

        $request->validate([
            'order_id'     => 'required|exists:orders,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
            'quick_tags'   => 'nullable|array',
            'quick_tags.*' => 'string|max:100',
            'result_image' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp',
        ]);

        $order = DB::table('orders')
            ->where('id', $request->order_id)
            ->where('client_id', session('user_id'))
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return back()->with('error', 'Order tidak valid.');
        }

        $imagePath = null;
        if ($request->hasFile('result_image')) {
            $imagePath = $request->file('result_image')->store('review-images', 'public');
        }

        DB::table('reviews')->insert([
            'order_id'      => $order->id,
            'client_id'     => session('user_id'),
            'commission_id' => $order->commission_id,
            'rating'        => $request->rating,
            'comment'       => $request->comment,
            'quick_tags'    => json_encode($request->quick_tags ?? []),
            'result_image'  => $imagePath,
            'status'        => 'pending',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('orders.client')
            ->with('success', 'Review berhasil dikirim! Menunggu persetujuan artist.');
    }

    // Artist: approve review
    public function approve($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('reviews')->where('id', $id)->update([
            'status'     => 'approved',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // Artist: reject review
    public function reject(Request $request, $id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('reviews')->where('id', $id)->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reason,
            'updated_at'    => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // Artist: hapus review
    public function destroy($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $review = DB::table('reviews')->where('id', $id)->first();
        if ($review && $review->result_image) {
            Storage::disk('public')->delete($review->result_image);
        }

        DB::table('reviews')->where('id', $id)->delete();

        return response()->json(['success' => true]);
    }
}