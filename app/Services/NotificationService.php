<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Simpan in-app notification + kirim email
     */
    public static function send(int $userId, string $type, string $title, string $message, string $url = null)
    {
        // Simpan ke database (in-app)
        DB::table('notifications')->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'url'        => $url,
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kirim email
        $user = DB::table('users')->where('id', $userId)->first();
        if ($user && $user->email) {
            self::sendEmail($user, $title, $message, $url);
        }
    }

    private static function sendEmail($user, string $title, string $message, string $url = null)
    {
        try {
            Mail::send([], [], function ($mail) use ($user, $title, $message, $url) {
                $btnHtml = $url ? '
                    <a href="' . url($url) . '"
                        style="display:inline-block;margin-top:16px;background:#f97316;color:white;padding:12px 24px;border-radius:12px;font-weight:700;text-decoration:none;font-size:0.875rem;">
                        Lihat Detail
                    </a>' : '';

                $mail->to($user->email, $user->name)
                    ->subject($title . ' — ArtSpace')
                    ->html('
                        <div style="font-family:sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#f9fafb;border-radius:16px;">
                            <div style="text-align:center;margin-bottom:24px;">
                                <div style="display:inline-flex;align-items:center;gap:8px;">
                                    <div style="width:36px;height:36px;background:#f97316;border-radius:10px;display:inline-block;"></div>
                                    <span style="font-weight:800;font-size:1.2rem;color:#21212e;">ArtSpace</span>
                                </div>
                            </div>
                            <h2 style="color:#21212e;font-size:1rem;margin-bottom:8px;">' . $title . '</h2>
                            <p style="color:#6b7280;font-size:0.875rem;line-height:1.6;margin-bottom:8px;">
                                Halo <strong>' . $user->name . '</strong>,
                            </p>
                            <p style="color:#6b7280;font-size:0.875rem;line-height:1.6;">
                                ' . $message . '
                            </p>
                            ' . $btnHtml . '
                            <hr style="margin:24px 0;border:none;border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af;font-size:0.75rem;text-align:center;">
                                Email ini dikirim otomatis oleh ArtSpace. Jangan balas email ini.
                            </p>
                        </div>
                    ');
            });
        } catch (\Exception $e) {
            // Gagal kirim email tidak boleh break aplikasi
            Log::warning('Email notification failed: ' . $e->getMessage());
        }
    }
}