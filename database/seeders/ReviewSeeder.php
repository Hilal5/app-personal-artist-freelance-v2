<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $artist     = DB::table('users')->where('role', 'artist')->first();
        $clients    = DB::table('users')->where('role', 'client')->get();
        $commission = DB::table('commissions')->first();

        if (!$artist || $clients->isEmpty() || !$commission) {
            $this->command->warn('Pastikan ada artist, client, dan commission dulu.');
            return;
        }

        $dummyReviews = [
            [
                'rating'  => 5,
                'comment' => 'Sangat puas! Hasilnya melebihi ekspektasi saya. Artist sangat profesional dan responsif. Definitely akan order lagi!',
                'tags'    => ['Komunikasi bagus', 'Pengerjaan cepat', 'Hasil memuaskan', 'Sangat profesional'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 5,
                'comment' => 'Luar biasa! Karakter OC saya digambar dengan sangat detail. Revisi ditangani dengan baik dan cepat.',
                'tags'    => ['Ramah & responsif', 'Revisi ditangani dengan baik', 'Detail terjaga', 'Recommended!'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 5,
                'comment' => 'Keren banget hasilnya! Persis seperti referensi yang aku kasih. Komunikasinya juga enak.',
                'tags'    => ['Komunikasi bagus', 'Sesuai ekspektasi', 'Detail terjaga', 'Harga worth it'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 5,
                'comment' => 'Sudah 3x order di sini dan selalu memuaskan. Artist benar-benar paham style yang aku mau. Highly recommended!',
                'tags'    => ['Recommended!', 'Sangat profesional', 'Hasil memuaskan', 'Ramah & responsif'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 4,
                'comment' => 'Overall bagus, hasilnya sesuai request. Sedikit lambat tapi kualitas terjaga dengan baik.',
                'tags'    => ['Hasil memuaskan', 'Sesuai ekspektasi'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 4,
                'comment' => 'Hasilnya bagus dan sesuai style yang diminta. Komunikasi lancar dan artist mau dengerin feedback.',
                'tags'    => ['Komunikasi bagus', 'Sesuai ekspektasi', 'Ramah & responsif'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 3,
                'comment' => 'Hasilnya cukup oke, tapi ada beberapa detail yang kurang sesuai request awal.',
                'tags'    => ['Kurang sesuai request', 'Komunikasi kurang'],
                'status'  => 'approved',
            ],
            [
                'rating'  => 2,
                'comment' => 'Kecewa dengan hasilnya, tidak sesuai dengan yang dijanjikan. Revisi juga lama banget.',
                'tags'    => ['Tidak sesuai ekspektasi', 'Revisi tidak ditangani', 'Pengerjaan lambat'],
                'status'  => 'pending',
            ],
        ];

        // Hapus semua review lama dari seeder (opsional, biar bisa re-run)
        // DB::table('reviews')->truncate();

        foreach ($dummyReviews as $i => $rev) {
            // Ambil client secara bergilir
            $client = $clients[$i % $clients->count()];

            // Selalu buat order baru untuk setiap review dummy
            $orderId = DB::table('orders')->insertGetId([
                'order_number'    => 'INV-SEED-' . strtoupper(uniqid()),
                'commission_id'   => $commission->id,
                'client_id'       => $client->id,
                'artist_id'       => $artist->id,
                'tier'            => collect(['basic', 'standard', 'premium'])->random(),
                'price'           => $commission->tier_basic_price,
                'discount'        => 0,
                'final_price'     => $commission->tier_basic_price,
                'description'     => 'Dummy order untuk review seeder #' . ($i + 1),
                'payment_method'  => collect(['bri', 'dana', 'gopay', 'shopeepay'])->random(),
                'status'          => 'completed',
                'confirmed_at'    => now()->subDays(rand(10, 30)),
                'completed_at'    => now()->subDays(rand(3, 9)),
                'created_at'      => now()->subDays(rand(14, 40)),
                'updated_at'      => now()->subDays(rand(3, 9)),
            ]);

            // Insert review
            DB::table('reviews')->insert([
                'order_id'      => $orderId,
                'client_id'     => $client->id,
                'commission_id' => $commission->id,
                'rating'        => $rev['rating'],
                'comment'       => $rev['comment'],
                'quick_tags'    => json_encode($rev['tags']),
                'result_image'  => null,
                'status'        => $rev['status'],
                'created_at'    => now()->subDays(rand(1, 10)),
                'updated_at'    => now()->subDays(rand(0, 5)),
            ]);

            $this->command->info("Review #" . ($i + 1) . " (rating {$rev['rating']}) — berhasil ditambahkan.");
        }

        $this->command->info('✅ Review seeder selesai! Total: ' . count($dummyReviews) . ' review.');
    }
}