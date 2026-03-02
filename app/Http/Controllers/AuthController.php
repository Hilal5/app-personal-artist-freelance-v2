<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->with('login_error', 'Email atau password salah.')
                ->with('show_login_modal', true);
        }

        session([
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
        ]);

        return redirect()->route('artist.profile');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('artist.profile');
    }

    public function register(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:100',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
    ], [
        'name.required'      => 'Nama wajib diisi.',
        'email.required'     => 'Email wajib diisi.',
        'email.unique'       => 'Email sudah terdaftar.',
        'password.required'  => 'Password wajib diisi.',
        'password.min'       => 'Password minimal 6 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    $id = DB::table('users')->insertGetId([
        'name'              => $request->name,
        'email'             => $request->email,
        'password'          => Hash::make($request->password),
        'role'              => 'client',
        'commission_status' => 'open',
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    session([
        'user_id'   => $id,
        'user_name' => $request->name,
        'user_role' => 'client',
    ]);

    return redirect()->route('artist.profile')
        ->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $request->name . '!');
}
}