<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Inject order counts ke semua view
        View::composer('*', function ($view) {
            if (session('user_role') === 'artist') {
                $artistId = session('user_id');

                $orderCounts = [
                    'all'             => DB::table('orders')->where('artist_id', $artistId)->count(),
                    'pending'         => DB::table('orders')->where('artist_id', $artistId)->where('status', 'pending')->count(),
                    'confirmed'       => DB::table('orders')->where('artist_id', $artistId)->where('status', 'confirmed')->count(),
                    'waiting_payment' => DB::table('orders')->where('artist_id', $artistId)->where('status', 'waiting_payment')->count(),
                    'paid'            => DB::table('orders')->where('artist_id', $artistId)->where('status', 'paid')->count(),
                    'completed'       => DB::table('orders')->where('artist_id', $artistId)->where('status', 'completed')->count(),
                ];

                $view->with('orderCounts', $orderCounts);

            } elseif (session('user_id') && session('user_role') === 'client') {
                $clientId = session('user_id');

                $orderCounts = [
                    'all'       => DB::table('orders')->where('client_id', $clientId)->count(),
                    'pending'   => DB::table('orders')->where('client_id', $clientId)->where('status', 'pending')->count(),
                    'active'    => DB::table('orders')->where('client_id', $clientId)->whereIn('status', ['confirmed', 'waiting_payment', 'paid'])->count(),
                    'completed' => DB::table('orders')->where('client_id', $clientId)->where('status', 'completed')->count(),
                ];

                $view->with('orderCounts', $orderCounts);

            } else {
                $view->with('orderCounts', [
                    'all' => 0, 'pending' => 0, 'confirmed' => 0,
                    'waiting_payment' => 0, 'paid' => 0, 'completed' => 0, 'active' => 0,
                ]);
            }
        });
    }
}