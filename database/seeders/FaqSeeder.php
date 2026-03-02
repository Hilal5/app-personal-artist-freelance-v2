<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $artist = DB::table('users')->where('role', 'artist')->first();
        if (!$artist) {
            $this->command->warn('Tidak ada artist.');
            return;
        }

        $faqs = [
            // Cara Order Commission
            [
                'category' => 'Cara Order Commission',
                'question' => 'Bagaimana cara memesan commission?',
                'answer'   => 'Pilih commission yang kamu inginkan, pilih paket (Basic/Standard/Premium), pilih metode pembayaran, lalu klik "Order Sekarang". Isi form detail request kamu dan submit. Artist akan mengkonfirmasi ordermu dalam 1x24 jam.',
                'order'    => 1,
            ],
            [
                'category' => 'Cara Order Commission',
                'question' => 'Apakah saya perlu login untuk memesan?',
                'answer'   => 'Ya, kamu perlu mendaftar dan login sebagai client terlebih dahulu. Pendaftaran gratis dan hanya membutuhkan email dan password.',
                'order'    => 2,
            ],
            [
                'category' => 'Cara Order Commission',
                'question' => 'Berapa maksimal slot order yang tersedia?',
                'answer'   => 'Setiap commission memiliki batas slot yang ditentukan oleh artist. Jika slot penuh, commission akan otomatis tertutup dan kamu perlu menunggu hingga ada slot kosong.',
                'order'    => 3,
            ],
            [
                'category' => 'Cara Order Commission',
                'question' => 'Bisakah saya membatalkan order?',
                'answer'   => 'Order bisa dibatalkan selama statusnya masih Pending atau Confirmed (sebelum artist mulai mengerjakan). Setelah status berubah ke In Progress, pembatalan tidak bisa dilakukan.',
                'order'    => 4,
            ],

            // Cara Pembayaran
            [
                'category' => 'Cara Pembayaran',
                'question' => 'Metode pembayaran apa saja yang tersedia?',
                'answer'   => 'Tersedia berbagai metode pembayaran: Transfer Bank (BRI, SeaBank, Bank Jago) dan e-Wallet (DANA, GoPay, ShopeePay). Detail nomor rekening akan diberikan artist melalui chat.',
                'order'    => 1,
            ],
            [
                'category' => 'Cara Pembayaran',
                'question' => 'Kapan saya harus membayar?',
                'answer'   => 'Pembayaran dilakukan setelah artist selesai mengerjakan dan mengirimkan preview hasil karya melalui chat. Artist akan memberikan notifikasi bahwa order siap dibayar.',
                'order'    => 2,
            ],
            [
                'category' => 'Cara Pembayaran',
                'question' => 'Bagaimana cara upload bukti pembayaran?',
                'answer'   => 'Setelah transfer, masuk ke halaman My Orders, temukan order yang bersangkutan, lalu klik "Upload Bukti Bayar". Upload foto/screenshot bukti transfer dan tambahkan catatan jika perlu.',
                'order'    => 3,
            ],

            // Revisi & Refund
            [
                'category' => 'Revisi & Refund',
                'question' => 'Berapa kali saya bisa minta revisi?',
                'answer'   => 'Jumlah revisi tergantung paket yang dipilih. Detail revisi tertera di deskripsi masing-masing paket. Diskusikan kebutuhan revisi dengan artist melalui fitur chat.',
                'order'    => 1,
            ],
            [
                'category' => 'Revisi & Refund',
                'question' => 'Apakah ada kebijakan refund?',
                'answer'   => 'Refund dapat dipertimbangkan jika order dibatalkan sebelum artist mulai mengerjakan. Setelah pengerjaan dimulai, refund tidak tersedia. Hubungi artist melalui chat untuk diskusi lebih lanjut.',
                'order'    => 2,
            ],
            [
                'category' => 'Revisi & Refund',
                'question' => 'Bagaimana jika hasil tidak sesuai ekspektasi?',
                'answer'   => 'Komunikasikan dengan jelas referensi dan ekspektasi kamu di awal order melalui form request dan chat. Jika ada ketidaksesuaian, diskusikan langsung dengan artist untuk solusi terbaik.',
                'order'    => 3,
            ],

            // Estimasi Waktu
            [
                'category' => 'Estimasi Waktu',
                'question' => 'Berapa lama waktu pengerjaan commission?',
                'answer'   => 'Estimasi waktu tertera di setiap halaman commission. Waktu pengerjaan bervariasi tergantung kompleksitas dan paket yang dipilih. Artist akan memberikan update progress melalui chat.',
                'order'    => 1,
            ],
            [
                'category' => 'Estimasi Waktu',
                'question' => 'Bisakah saya request deadline khusus?',
                'answer'   => 'Ya, kamu bisa menentukan deadline saat mengisi form order. Namun perlu dikonfirmasi dengan artist terlebih dahulu apakah deadline tersebut memungkinkan.',
                'order'    => 2,
            ],
            [
                'category' => 'Estimasi Waktu',
                'question' => 'Apa yang terjadi jika deadline terlewat?',
                'answer'   => 'Artist akan berusaha semaksimal mungkin menyelesaikan tepat waktu. Jika ada kendala, artist akan memberitahu melalui chat. Kamu bisa mendiskusikan solusi bersama artist.',
                'order'    => 3,
            ],

            // Format File
            [
                'category' => 'Format File yang Diterima',
                'question' => 'Format file referensi apa yang bisa diupload?',
                'answer'   => 'Kamu bisa mengupload referensi dalam format JPG, JPEG, PNG, GIF, dan WEBP. Ukuran maksimal per file adalah 20MB dan maksimal 20 file per order.',
                'order'    => 1,
            ],
            [
                'category' => 'Format File yang Diterima',
                'question' => 'Format file apa yang akan saya terima?',
                'answer'   => 'File hasil karya biasanya dikirim dalam format PNG (transparan) atau JPG resolusi tinggi. Format lain seperti PSD atau file layered bisa didiskusikan dengan artist melalui chat.',
                'order'    => 2,
            ],
            [
                'category' => 'Format File yang Diterima',
                'question' => 'Berapa resolusi file yang akan saya terima?',
                'answer'   => 'Resolusi file tergantung paket yang dipilih. Paket Premium biasanya mendapatkan resolusi lebih tinggi. Detail resolusi tertera di deskripsi paket atau bisa ditanyakan langsung ke artist.',
                'order'    => 3,
            ],

            // Kontak & Support
            [
                'category' => 'Kontak & Support',
                'question' => 'Bagaimana cara menghubungi artist?',
                'answer'   => 'Gunakan fitur Chat yang tersedia di navbar. Kamu bisa mengirim pesan teks maupun file. Artist biasanya merespons dalam beberapa jam.',
                'order'    => 1,
            ],
            [
                'category' => 'Kontak & Support',
                'question' => 'Apakah ada dukungan pelanggan?',
                'answer'   => 'Untuk pertanyaan umum, kamu bisa menghubungi artist langsung melalui fitur Chat atau melalui sosial media yang tertera di halaman Profile.',
                'order'    => 2,
            ],
            [
                'category' => 'Kontak & Support',
                'question' => 'Bagaimana cara meninggalkan review?',
                'answer'   => 'Setelah order selesai (status Completed), kamu akan melihat tombol "Beri Review" di halaman My Orders. Klik tombol tersebut untuk memberikan rating dan ulasan.',
                'order'    => 3,
            ],
        ];

        foreach ($faqs as $faq) {
            DB::table('faqs')->insert([
                'user_id'   => $artist->id,
                'category'  => $faq['category'],
                'question'  => $faq['question'],
                'answer'    => $faq['answer'],
                'order'     => $faq['order'],
                'is_active' => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);
        }

        $this->command->info('✅ FAQ seeder selesai! Total: ' . count($faqs) . ' FAQ.');
    }
}