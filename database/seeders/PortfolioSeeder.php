<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $artist = DB::table('users')->where('role', 'artist')->first();
        if (!$artist) {
            $this->command->warn('Tidak ada artist.');
            return;
        }

        $portfolios = [
            [
                'title'        => 'Anime Character OC — Sakura',
                'description'  => 'Original character design bertema sakura dengan warna pastel. Dibuat full digital dari sketch hingga coloring.',
                'category'     => 'Character Design',
                'software'     => 'Procreate',
                'client_name'  => 'Budi Client',
                'created_date' => '2026-01-15',
                'status'       => 'published',
                'tags'         => ['anime', 'oc', 'sakura', 'pastel'],
            ],
            [
                'title'        => 'Fantasy Landscape — Enchanted Forest',
                'description'  => 'Pemandangan hutan ajaib dengan nuansa magis. Terinspirasi dari game RPG klasik.',
                'category'     => 'Concept Art',
                'software'     => 'Photoshop',
                'client_name'  => null,
                'created_date' => '2026-01-20',
                'status'       => 'published',
                'tags'         => ['landscape', 'fantasy', 'rpg', 'environment'],
            ],
            [
                'title'        => 'Chibi Commission — Couple',
                'description'  => 'Chibi couple commission untuk anniversary. Style cute dengan warna cerah.',
                'category'     => 'Chibi',
                'software'     => 'Clip Studio Paint',
                'client_name'  => 'Ani Collector',
                'created_date' => '2026-01-28',
                'status'       => 'published',
                'tags'         => ['chibi', 'couple', 'cute', 'anniversary'],
            ],
            [
                'title'        => 'Portrait Realism — Digital Oil',
                'description'  => 'Portrait digital dengan teknik oil painting. Waktu pengerjaan 20+ jam.',
                'category'     => 'Portrait',
                'software'     => 'Photoshop',
                'client_name'  => 'Charlie Art Lover',
                'created_date' => '2026-02-03',
                'status'       => 'published',
                'tags'         => ['portrait', 'realism', 'oil', 'digital'],
            ],
            [
                'title'        => 'Fanart — Genshin Impact',
                'description'  => 'Fanart karakter Hu Tao dari Genshin Impact dengan style semi-realis.',
                'category'     => 'Fanart',
                'software'     => 'Procreate',
                'client_name'  => null,
                'created_date' => '2026-02-10',
                'status'       => 'published',
                'tags'         => ['fanart', 'genshin', 'hutao', 'semirealis'],
            ],
            [
                'title'        => 'Concept Design — Mech Warrior',
                'description'  => 'Desain karakter mech warrior untuk proyek game indie. Meliputi front, side, dan back view.',
                'category'     => 'Concept Art',
                'software'     => 'Clip Studio Paint',
                'client_name'  => 'Dewi Art Enthusiast',
                'created_date' => '2026-02-18',
                'status'       => 'published',
                'tags'         => ['mech', 'sci-fi', 'concept', 'game'],
            ],
            [
                'title'        => 'Ilustrasi Buku — Children Story',
                'description'  => 'Ilustrasi untuk buku cerita anak dengan tema petualangan. 5 halaman full color.',
                'category'     => 'Ilustrasi',
                'software'     => 'Procreate',
                'client_name'  => 'Eko Gallery',
                'created_date' => '2026-02-25',
                'status'       => 'published',
                'tags'         => ['ilustrasi', 'children', 'book', 'petualangan'],
            ],
            [
                'title'        => 'Speed Paint — Sunset Cityscape',
                'description'  => 'Speed paint pemandangan kota saat sunset. Dikerjakan dalam 2 jam real time.',
                'category'     => 'Concept Art',
                'software'     => 'Photoshop',
                'client_name'  => null,
                'created_date' => '2026-03-01',
                'status'       => 'draft',
                'tags'         => ['speedpaint', 'cityscape', 'sunset'],
            ],
        ];

        // Placeholder image URLs (warna berbeda per kategori)
        $placeholders = [
            'Character Design' => 'https://picsum.photos/seed/char/600/800',
            'Concept Art'      => 'https://picsum.photos/seed/concept/800/600',
            'Chibi'            => 'https://picsum.photos/seed/chibi/600/600',
            'Portrait'         => 'https://picsum.photos/seed/portrait/600/750',
            'Fanart'           => 'https://picsum.photos/seed/fanart/600/800',
            'Ilustrasi'        => 'https://picsum.photos/seed/illust/700/500',
        ];

        foreach ($portfolios as $p) {
            $portfolioId = DB::table('portfolios')->insertGetId([
                'user_id'      => $artist->id,
                'title'        => $p['title'],
                'description'  => $p['description'],
                'category'     => $p['category'],
                'software'     => $p['software'],
                'client_name'  => $p['client_name'],
                'created_date' => $p['created_date'],
                'status'       => $p['status'],
                'views'        => rand(10, 500),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Insert tags
            foreach ($p['tags'] as $tag) {
                DB::table('portfolio_tags')->insert([
                    'portfolio_id' => $portfolioId,
                    'tag'          => $tag,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            $this->command->info("Portfolio '{$p['title']}' berhasil ditambahkan.");
        }

        $this->command->info('✅ Portfolio seeder selesai!');
    }
}