<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

    // Remember Me — simpan cookie 30 hari
    if ($request->boolean('remember')) {
        cookie()->queue('remember_user', encrypt($user->id), 60 * 24 * 30);
    }

    // Track last login
    // DB::table('users')->where('id', $user->id)->update([
    //     'last_login_at' => now(),
    //     'updated_at'    => now(),
    // ]);

    return redirect()->route('artist.profile');
}

public function logout()
{
    // Hapus remember cookie
    cookie()->queue(cookie()->forget('remember_user'));
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


// Tampilkan form forgot password
public function forgotForm()
{
    return view('auth.forgot-password');
}

// Kirim email reset
public function forgotSend(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ], [
        'email.required' => 'Email wajib diisi.',
        'email.email'    => 'Format email tidak valid.',
    ]);

    $user = DB::table('users')->where('email', $request->email)->first();

    // Selalu tampilkan pesan sukses meski email tidak ada (security)
    if (!$user) {
        return back()->with('success', 'Jika email terdaftar, link reset akan dikirim.');
    }

    // Hapus token lama
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    // Buat token baru
    $token = Str::random(64);

    DB::table('password_reset_tokens')->insert([
        'email'      => $request->email,
        'token'      => hash('sha256', $token),
        'created_at' => now(),
    ]);

    $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

    // Kirim email
    Mail::send([], [], function ($message) use ($user, $resetUrl) {
        $message->to($user->email, $user->name)
            ->subject('Reset Password — ArtSpace')
            ->html('
                <div style="font-family:sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#f9fafb;border-radius:16px;">
                    <div style="text-align:center;margin-bottom:24px;">
                        <div style="display:inline-flex;align-items:center;gap:8px;">
                            <div style="width:36px;height:36px;background:#f97316;border-radius:10px;display:inline-block;"></div>
                            <span style="font-weight:800;font-size:1.2rem;color:#21212e;">ArtSpace</span>
                        </div>
                    </div>
                    <h2 style="color:#21212e;font-size:1.1rem;margin-bottom:8px;">Reset Password</h2>
                    <p style="color:#6b7280;font-size:0.875rem;margin-bottom:24px;">
                        Halo <strong>' . $user->name . '</strong>, kamu meminta reset password.<br>
                        Klik tombol di bawah untuk membuat password baru.
                    </p>
                    <a href="' . $resetUrl . '"
                        style="display:block;text-align:center;background:#f97316;color:white;padding:14px 24px;border-radius:12px;font-weight:700;text-decoration:none;font-size:0.875rem;">
                        Reset Password Sekarang
                    </a>
                    <p style="color:#9ca3af;font-size:0.75rem;margin-top:24px;text-align:center;">
                        Link berlaku selama <strong>60 menit</strong>.<br>
                        Jika kamu tidak meminta reset, abaikan email ini.
                    </p>
                </div>
            ');
    });

    return back()->with('success', 'Link reset password telah dikirim ke email kamu.');
}

// Tampilkan form reset password
public function resetForm(Request $request, $token)
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->query('email'),
    ]);
}

// Proses update password
public function resetUpdate(Request $request)
{
    $request->validate([
        'email'                 => 'required|email',
        'token'                 => 'required',
        'password'              => 'required|min:6|confirmed',
        'password_confirmation' => 'required',
    ], [
        'password.min'       => 'Password minimal 6 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    // Cari token
    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$record) {
        return back()->withErrors(['email' => 'Token tidak valid atau sudah kadaluarsa.']);
    }

    // Cek token cocok
    if (!hash_equals($record->token, hash('sha256', $request->token))) {
        return back()->withErrors(['email' => 'Token tidak valid.']);
    }

    // Cek expired (60 menit)
    if (now()->diffInMinutes($record->created_at) > 60) {
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return back()->withErrors(['email' => 'Link reset sudah kadaluarsa. Minta ulang.']);
    }

    // Update password
    DB::table('users')
        ->where('email', $request->email)
        ->update([
            'password'   => bcrypt($request->password),
            'updated_at' => now(),
        ]);

    // Hapus token
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return redirect()->route('artist.profile')
        ->with('show_login_modal', true)
        ->with('success', 'Password berhasil diubah! Silakan login.');
}

}