<?php
// database/seeders/AdminSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Data Admin (artist)
        DB::table('users')->insert([
            'name'       => 'Admin Artist',
            'email'      => 'admin@artspace.com',
            'password'   => Hash::make('password'),
            'role'       => 'artist',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Data Clients (multiple)
        // $clients = [
        //     [
        //         'name'       => 'Budi Client',
        //         'email'      => 'client@artspace.com',
        //         'role'       => 'client',
        //     ],
        //     [
        //         'name'       => 'Ani Collector',
        //         'email'      => 'ani@example.com',
        //         'role'       => 'client',
        //     ],
        //     [
        //         'name'       => 'Charlie Art Lover',
        //         'email'      => 'charlie@example.com',
        //         'role'       => 'client',
        //     ],
        //     [
        //         'name'       => 'Dewi Art Enthusiast',
        //         'email'      => 'dewi@example.com',
        //         'role'       => 'client',
        //     ],
        //     [
        //         'name'       => 'Eko Gallery',
        //         'email'      => 'eko@example.com',
        //         'role'       => 'client',
        //     ],
        // ];

        // foreach ($clients as $client) {
        //     DB::table('users')->insert([
        //         'name'       => $client['name'],
        //         'email'      => $client['email'],
        //         'password'   => Hash::make('password'), // password default: 'password'
        //         'role'       => $client['role'],
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }
    }
}