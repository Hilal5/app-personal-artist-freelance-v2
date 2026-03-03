<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class CommissionController extends Controller
{
    // Halaman list commission (publik)
    public function index()
    {
        $commissions = DB::table('commissions')
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($c) {
                $c->media = DB::table('commission_media')
                    ->where('commission_id', $c->id)
                    ->get();
                return $c;
            });

        return view('commission.index', compact('commissions'));
    }

    // Detail commission
    public function show($id)
    {
        $commission = DB::table('commissions')->where('id', $id)->first();
        if (!$commission) abort(404);

        $commission->media = DB::table('commission_media')
            ->where('commission_id', $id)
            ->get();

        return view('commission.show', compact('commission'));
    }

    // Form order
    public function orderForm($id)
    {
        if (!session('user_id')) {
            return redirect()->route('commission.index')
                ->with('login_error', 'Login dulu untuk melakukan order.')
                ->with('show_login_modal', true);
        }

        $commission = DB::table('commissions')->where('id', $id)->first();
        if (!$commission || $commission->status === 'closed') {
            return redirect()->route('commission.index')->with('error', 'Commission tidak tersedia.');
        }

        $tier = request('tier', 'basic');

        return view('commission.order', compact('commission', 'tier'));
    }

    // Proses order
    public function orderStore(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('commission.index');
        }

        $request->validate([
            'commission_id'  => 'required|exists:commissions,id',
            'tier'           => 'required|in:basic,standard,premium',
            'description'    => 'required|string|max:2000',
            'notes'          => 'nullable|string|max:1000',
            'client_deadline'=> 'nullable|date|after:today',
            'payment_method' => 'required|in:bri,seabank,bank_jago,dana,gopay,shopeepay',
            'references.*'   => 'nullable|file|max:20480|mimes:jpg,jpeg,png,gif,webp,mp4,mov',
        ]);

        $commission = DB::table('commissions')->where('id', $request->commission_id)->first();

        // Cek slot
        if ($commission->used_slots >= $commission->max_slots) {
            return back()->with('error', 'Slot sudah penuh.');
        }

        // Hitung harga
        $priceKey    = 'tier_' . $request->tier . '_price';
        $discountKey = 'tier_' . $request->tier . '_discount';
        $price       = $commission->$priceKey;
        $discount    = $commission->$discountKey;
        $finalPrice  = $price - ($price * $discount / 100);

        // Buat order number
        $orderNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Artist ID
        $artist = DB::table('users')->where('role', 'artist')->first();

        $orderId = DB::table('orders')->insertGetId([
            'order_number'   => $orderNumber,
            'commission_id'  => $commission->id,
            'client_id'      => session('user_id'),
            'artist_id'      => $artist->id,
            'tier'           => $request->tier,
            'price'          => $price,
            'discount'       => $discount,
            'final_price'    => $finalPrice,
            'description'    => $request->description,
            'notes'          => $request->notes,
            'client_deadline'=> $request->client_deadline,
            'payment_method' => $request->payment_method,
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        NotificationService::send(
            $artist->id,
            'order_new',
            'Order Baru Masuk!',
            'Ada order baru dari <strong>' . session('user_name') . '</strong> untuk commission <strong>' . $commission->title . '</strong> (Tier: ' . ucfirst($request->tier) . '). Segera cek dan konfirmasi.',
            '/orders/artist'
        );

        // Update used_slots
        DB::table('commissions')
            ->where('id', $commission->id)
            ->increment('used_slots');

        // Upload referensi
        if ($request->hasFile('references')) {
            foreach ($request->file('references') as $file) {
                $path = $file->store('order-references', 'public');
                DB::table('order_references')->insert([
                    'order_id'   => $orderId,
                    'file_path'  => $path,
                    'file_type'  => $file->getMimeType(),
                    'file_name'  => $file->getClientOriginalName(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('orders.client')
            ->with('success', 'Order berhasil dibuat! Nomor order: ' . $orderNumber);
    }

    // ===== ARTIST: Manage Commission =====

    public function manageIndex()
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }

        $commissions = DB::table('commissions')
            ->where('user_id', session('user_id'))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($c) {
                $c->media = DB::table('commission_media')
                    ->where('commission_id', $c->id)
                    ->get();
                return $c;
            });

        return view('commission.manage', compact('commissions'));
    }

    public function create()
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }
        return view('commission.create');
    }

    public function store(Request $request)
    {
        if (session('user_role') !== 'artist') {
            return redirect()->route('artist.profile');
        }

        $request->validate([
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'category'              => 'required|string|max:100',
            'estimated_days'        => 'required|integer|min:1',
            'max_slots'             => 'required|integer|min:1',
            'status'                => 'required|in:open,closed',
            'tier_basic_name'       => 'required|string|max:100',
            'tier_basic_desc'       => 'nullable|string',
            'tier_basic_price'      => 'required|integer|min:0',
            'tier_basic_discount'   => 'nullable|integer|min:0|max:100',
            'tier_standard_name'    => 'required|string|max:100',
            'tier_standard_desc'    => 'nullable|string',
            'tier_standard_price'   => 'required|integer|min:0',
            'tier_standard_discount'=> 'nullable|integer|min:0|max:100',
            'tier_premium_name'     => 'required|string|max:100',
            'tier_premium_desc'     => 'nullable|string',
            'tier_premium_price'    => 'required|integer|min:0',
            'tier_premium_discount' => 'nullable|integer|min:0|max:100',
            'media'   => 'nullable|array|max:20',
            'media.*' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov',
        ]);

        $commissionId = DB::table('commissions')->insertGetId([
            'user_id'               => session('user_id'),
            'title'                 => $request->title,
            'description'           => $request->description,
            'category'              => $request->category,
            'estimated_days'        => $request->estimated_days,
            'max_slots'             => $request->max_slots,
            'status'                => $request->status,
            'tier_basic_name'       => $request->tier_basic_name,
            'tier_basic_desc'       => $request->tier_basic_desc,
            'tier_basic_price'      => $request->tier_basic_price,
            'tier_basic_discount'   => $request->tier_basic_discount ?? 0,
            'tier_standard_name'    => $request->tier_standard_name,
            'tier_standard_desc'    => $request->tier_standard_desc,
            'tier_standard_price'   => $request->tier_standard_price,
            'tier_standard_discount'=> $request->tier_standard_discount ?? 0,
            'tier_premium_name'     => $request->tier_premium_name,
            'tier_premium_desc'     => $request->tier_premium_desc,
            'tier_premium_price'    => $request->tier_premium_price,
            'tier_premium_discount' => $request->tier_premium_discount ?? 0,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('commission-media', 'public');
                DB::table('commission_media')->insert([
                    'commission_id' => $commissionId,
                    'file_path'     => $path,
                    'file_type'     => str_contains($file->getMimeType(), 'video') ? 'video' : 'image',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        return redirect()->route('commission.manage')
            ->with('success', 'Commission berhasil dibuat!');
    }

    public function edit($id)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $commission = DB::table('commissions')
            ->where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$commission) abort(404);

        $commission->media = DB::table('commission_media')
            ->where('commission_id', $id)->get();

        return view('commission.edit', compact('commission'));
    }

    public function update(Request $request, $id)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $commission = DB::table('commissions')
            ->where('id', $id)
            ->where('user_id', session('user_id'))
            ->first();

        if (!$commission) abort(404);

        $request->validate([
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'category'              => 'required|string|max:100',
            'estimated_days'        => 'required|integer|min:1',
            'max_slots'             => 'required|integer|min:1',
            'status'                => 'required|in:open,closed',
            'tier_basic_price'      => 'required|integer|min:0',
            'tier_basic_discount'   => 'nullable|integer|min:0|max:100',
            'tier_standard_price'   => 'required|integer|min:0',
            'tier_standard_discount'=> 'nullable|integer|min:0|max:100',
            'tier_premium_price'    => 'required|integer|min:0',
            'tier_premium_discount' => 'nullable|integer|min:0|max:100',
            'media'   => 'nullable|array|max:20',
            'media.*' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov',
        ]);

        DB::table('commissions')->where('id', $id)->update([
            'title'                 => $request->title,
            'description'           => $request->description,
            'category'              => $request->category,
            'estimated_days'        => $request->estimated_days,
            'max_slots'             => $request->max_slots,
            'status'                => $request->status,
            'tier_basic_name'       => $request->tier_basic_name,
            'tier_basic_desc'       => $request->tier_basic_desc,
            'tier_basic_price'      => $request->tier_basic_price,
            'tier_basic_discount'   => $request->tier_basic_discount ?? 0,
            'tier_standard_name'    => $request->tier_standard_name,
            'tier_standard_desc'    => $request->tier_standard_desc,
            'tier_standard_price'   => $request->tier_standard_price,
            'tier_standard_discount'=> $request->tier_standard_discount ?? 0,
            'tier_premium_name'     => $request->tier_premium_name,
            'tier_premium_desc'     => $request->tier_premium_desc,
            'tier_premium_price'    => $request->tier_premium_price,
            'tier_premium_discount' => $request->tier_premium_discount ?? 0,
            'updated_at'            => now(),
        ]);

        // Upload media baru
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('commission-media', 'public');
                DB::table('commission_media')->insert([
                    'commission_id' => $id,
                    'file_path'     => $path,
                    'file_type'     => str_contains($file->getMimeType(), 'video') ? 'video' : 'image',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        // Hapus media yang dipilih
        if ($request->delete_media) {
            foreach ($request->delete_media as $mediaId) {
                $media = DB::table('commission_media')->where('id', $mediaId)->first();
                if ($media) {
                    Storage::disk('public')->delete($media->file_path);
                    DB::table('commission_media')->where('id', $mediaId)->delete();
                }
            }
        }

        return redirect()->route('commission.manage')
            ->with('success', 'Commission berhasil diupdate!');
    }

    public function destroy($id)
    {
        if (session('user_role') !== 'artist') return redirect()->route('artist.profile');

        $media = DB::table('commission_media')->where('commission_id', $id)->get();
        foreach ($media as $m) {
            Storage::disk('public')->delete($m->file_path);
        }
        DB::table('commission_media')->where('commission_id', $id)->delete();
        DB::table('commissions')->where('id', $id)->delete();

        return redirect()->route('commission.manage')
            ->with('success', 'Commission berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        if (session('user_role') !== 'artist') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $commission  = DB::table('commissions')->where('id', $id)->first();
        $newStatus   = $commission->status === 'open' ? 'closed' : 'open';

        DB::table('commissions')->where('id', $id)->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }
}