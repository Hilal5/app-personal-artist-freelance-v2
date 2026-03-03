<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // ===== ARTIST: Manage Orders =====
    public function artist()
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }

        $status = request('status', 'all');

        $query = DB::table('orders')
            ->join('users as clients', 'orders.client_id', '=', 'clients.id')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->select(
                'orders.*',
                'clients.name as client_name',
                'commissions.title as commission_title'
            )
            ->where('orders.artist_id', session('user_id'))
            ->orderByDesc('orders.created_at');

        if ($status !== 'all') {
            $query->where('orders.status', $status);
        }

        $orders = $query->get()->map(function ($o) {
            $o->references = DB::table('order_references')
                ->where('order_id', $o->id)->get();
            $o->payment = DB::table('order_payments')
                ->where('order_id', $o->id)->latest()->first();
            return $o;
        });

        $counts = [
            'all'             => DB::table('orders')->where('artist_id', session('user_id'))->count(),
            'pending'         => DB::table('orders')->where('artist_id', session('user_id'))->where('status', 'pending')->count(),
            'confirmed'       => DB::table('orders')->where('artist_id', session('user_id'))->where('status', 'confirmed')->count(),
            'waiting_payment' => DB::table('orders')->where('artist_id', session('user_id'))->where('status', 'waiting_payment')->count(),
            'paid'            => DB::table('orders')->where('artist_id', session('user_id'))->where('status', 'paid')->count(),
            'completed'       => DB::table('orders')->where('artist_id', session('user_id'))->where('status', 'completed')->count(),
        ];

        return view('orders.artist', compact('orders', 'status', 'counts'));
    }

    // ===== CLIENT: My Orders =====
    public function client()
    {
        if (!session('user_id')) {
            return redirect()->route('commission.index')
                ->with('show_login_modal', true);
        }

        $orders = DB::table('orders')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->join('users as artists', 'orders.artist_id', '=', 'artists.id')
            ->select(
                'orders.*',
                'commissions.title as commission_title',
                'artists.name as artist_name'
            )
            ->where('orders.client_id', session('user_id'))
            ->orderByDesc('orders.created_at')
            ->get()
            ->map(function ($o) {
                $o->references = DB::table('order_references')
                    ->where('order_id', $o->id)->get();
                $o->payment = DB::table('order_payments')
                    ->where('order_id', $o->id)->latest()->first();
                return $o;
            });

        return view('orders.client', compact('orders'));
    }

    // ===== ARTIST: Konfirmasi order =====
    public function confirm($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('orders')->where('id', $id)->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
            'updated_at'   => now(),
        ]);

        $order = DB::table('orders')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->select('orders.*', 'commissions.title as commission_title')
            ->where('orders.id', $id)->first();

        NotificationService::send(
            $order->client_id,
            'order_confirmed',
            'Order Dikonfirmasi!',
            'Order kamu untuk commission <strong>' . $order->commission_title . '</strong> telah dikonfirmasi oleh artist. Proses pengerjaan sedang dimulai.',
            '/orders/client'
        );

        return response()->json(['success' => true, 'status' => 'confirmed']);
    }

    // ===== ARTIST: Tolak order =====
    public function reject(Request $request, $id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('orders')->where('id', $id)->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reason,
            'updated_at'    => now(),
        ]);

        // Kurangi used_slots
        $order = DB::table('orders')->where('id', $id)->first();
        DB::table('commissions')
            ->where('id', $order->commission_id)
            ->decrement('used_slots');

        $order = DB::table('orders')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->select('orders.*', 'commissions.title as commission_title')
            ->where('orders.id', $id)->first();

        NotificationService::send(
            $order->client_id,
            'order_rejected',
            'Order Ditolak',
            'Maaf, order kamu untuk commission <strong>' . $order->commission_title . '</strong> tidak dapat diproses.' . ($request->reason ? ' Alasan: ' . $request->reason : ''),
            '/orders/client'
        );

        return response()->json(['success' => true, 'status' => 'rejected']);
    }

    // ===== ARTIST: Tandai selesai (waiting payment) =====
    public function markDone($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('orders')->where('id', $id)->update([
            'status'     => 'waiting_payment',
            'updated_at' => now(),
        ]);

        $order = DB::table('orders')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->select('orders.*', 'commissions.title as commission_title')
            ->where('orders.id', $id)->first();

        NotificationService::send(
            $order->client_id,
            'order_payment',
            'Saatnya Melakukan Pembayaran!',
            'Commission <strong>' . $order->commission_title . '</strong> telah selesai dikerjakan! Silakan lakukan pembayaran sebesar <strong>Rp' . number_format($order->final_price, 0, ',', '.') . '</strong> dan upload bukti transfer.',
            '/orders/client'
        );

        return response()->json(['success' => true, 'status' => 'waiting_payment']);
    }

    public function confirmPayment($id)
{
    if (session('user_role') !== 'artist') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Ambil DULU sebelum update, dengan join supaya ada commission_title
    $order = DB::table('orders')
        ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
        ->select('orders.*', 'commissions.title as commission_title')
        ->where('orders.id', $id)
        ->first();

    if (!$order) {
        return response()->json(['error' => 'Order tidak ditemukan'], 404);
    }

    DB::table('orders')->where('id', $id)->update([
        'status'       => 'completed',
        'completed_at' => now(),
        'updated_at'   => now(),
    ]);

    DB::table('order_payments')
        ->where('order_id', $id)
        ->update(['status' => 'confirmed', 'updated_at' => now()]);

    // ✅ Tidak ada decrement di sini

    NotificationService::send(
        $order->client_id,
        'order_completed',
        'Order Selesai! 🎉',
        'Pembayaran untuk commission <strong>' . $order->commission_title . '</strong> telah dikonfirmasi. Order kamu resmi selesai! Jangan lupa berikan review untuk artist.',
        '/orders/client'
    );

    return response()->json(['success' => true, 'status' => 'completed']);
}

    // ===== CLIENT: Batalkan order =====
    public function cancel(Request $request, $id)
    {
        if (!session('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = DB::table('orders')
            ->where('id', $id)
            ->where('client_id', session('user_id'))
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order tidak ditemukan'], 404);
        }

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json(['error' => 'Order tidak bisa dibatalkan'], 422);
        }

        DB::table('orders')->where('id', $id)->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->reason,
            'updated_at'    => now(),
        ]);

        DB::table('commissions')
            ->where('id', $order->commission_id)
            ->decrement('used_slots');

        return response()->json(['success' => true]);
    }

    // ===== CLIENT: Upload bukti bayar =====
    public function uploadPayment(Request $request, $id)
    {
        if (!session('user_id')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'proof' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf',
            'notes' => 'nullable|string|max:500',
        ]);

        $order = DB::table('orders')
            ->where('id', $id)
            ->where('client_id', session('user_id'))
            ->first();

        if (!$order || $order->status !== 'waiting_payment') {
            return response()->json(['error' => 'Order tidak valid'], 422);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');

        DB::table('order_payments')->insert([
            'order_id'   => $id,
            'proof_path' => $path,
            'notes'      => $request->notes,
            'status'     => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('orders')->where('id', $id)->update([
            'status'     => 'paid',
            'updated_at' => now(),
        ]);

        $artist = DB::table('users')->where('role', 'artist')->first();
        $orderWithCommission = DB::table('orders')
            ->join('commissions', 'orders.commission_id', '=', 'commissions.id')
            ->select('orders.*', 'commissions.title as commission_title')
            ->where('orders.id', $id)->first();

        NotificationService::send(
            $artist->id,
            'payment_uploaded',
            'Bukti Pembayaran Diterima!',
            'Client <strong>' . session('user_name') . '</strong> telah mengupload bukti pembayaran untuk commission <strong>' . $orderWithCommission->commission_title . '</strong>. Segera verifikasi pembayaran.',
            '/orders/artist?status=paid'
        );

        return response()->json(['success' => true]);
    }
}