<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in, redirect to appropriate dashboard
        foreach (['coordinator', 'guru', 'wali_kelas'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return match ($guard) {
                    'coordinator' => redirect()->route('coordinator.dashboard'),
                    'guru' => redirect()->route('guru.dashboard'),
                    'wali_kelas' => redirect()->route('walikelas.dashboard'),
                };
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'account' => 'required',
            'password' => 'required',
        ]);

        // Look up user by username to determine their role
        $user = DB::table('users')->where('username', $request->account)->first();

        if (!$user) {
            return back()->with('error', 'Username atau password salah.');
        }

        // Check if needs_password_reset is 1
        if (isset($user->needs_password_reset) && $user->needs_password_reset) {
            session([
                'force_reset_username' => $user->username,
                'force_reset_guard' => $user->role === 'wali_kelas' ? 'wali_kelas' : ($user->role === 'coordinator' ? 'coordinator' : 'guru')
            ]);
            return redirect()->route('force-reset-password');
        }

        // Map DB role to auth guard
        $guardMap = [
            'coordinator' => 'coordinator',
            'guru' => 'guru',
            'wali_kelas' => 'wali_kelas',
        ];

        $role = $user->role;
        $guard = $guardMap[$role] ?? null;

        if (!$guard) {
            return back()->with('error', 'Role user tidak valid.');
        }

        $credentials = [
            'username' => $request->account,
            'password' => $request->password,
        ];

        if (Auth::guard($guard)->attempt($credentials)) {
            $authUser = Auth::guard($guard)->user();
            $request->session()->regenerate();

            session([
                'user_id' => $authUser->id,
                'role' => $guard,
                'nama' => $authUser->nama,
            ]);

            return match ($guard) {
                'wali_kelas' => redirect()->route('walikelas.dashboard'),
                'coordinator' => redirect()->route('coordinator.dashboard'),
                'guru' => redirect()->route('guru.dashboard'),
                default => redirect('/login'),
            };
        }

        return back()->with('error', 'Username atau password salah.');
    }

    public function logout(Request $request)
    {
        $role = session('role');
        if ($role && in_array($role, ['coordinator', 'guru', 'wali_kelas'])) {
            Auth::guard($role)->logout();
        } else {
            Auth::logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // ── Forgot Password ─────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Step 1: Look up user by username, send OTP to their email
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'method' => 'nullable|in:email,whatsapp'
        ]);

        $method = $request->input('method', 'whatsapp');

        $user = DB::table('users')->where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan.',
            ]);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete old OTPs for this username
        DB::table('password_resets_otp')->where('username', $request->username)->delete();

        // Store OTP (expires in 10 minutes)
        DB::table('password_resets_otp')->insert([
            'username' => $request->username,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        if ($method === 'email') {
            if (!$user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini belum memiliki email terdaftar. Hubungi Koordinator.',
                ]);
            }
            $email = $user->email;
            $parts = explode('@', $email);
            $name = $parts[0];
            $domain = $parts[1] ?? '';
            $maskedTarget = (strlen($name) <= 2 ? $name . '***' : substr($name, 0, 2) . str_repeat('*', max(3, strlen($name) - 2))) . '@' . $domain;

            try {
                Mail::raw("Kode OTP untuk reset password akun WR2School Anda:\n\n{$otp}\n\nKode ini berlaku selama 10 menit.\nJika Anda tidak meminta reset password, abaikan email ini.", function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('WR2School - Kode OTP Reset Password');
                });
            } catch (\Exception $e) {
                \Log::error('OTP Email Error: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()]);
            }
        } else {
            // WhatsApp
            if (!$user->no_hp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini belum memiliki nomor WhatsApp (no_hp) terdaftar. Hubungi Koordinator.',
                ]);
            }
            $target = $user->no_hp;
            $maskedTarget = substr($target, 0, 4) . str_repeat('*', max(2, strlen($target) - 6)) . substr($target, -2);
            
            $message = "Kode OTP untuk reset password akun WR2School Anda:\n\n*{$otp}*\n\nKode ini berlaku selama 10 menit.\nJika Anda tidak meminta reset password, abaikan pesan ini.";
            
            $token = env('FONNTE_TOKEN');
            if (!$token || $token === 'isi_token_fonnte_anda_disini') {
                return response()->json([
                    'success' => false,
                    'message' => 'Layanan OTP WhatsApp belum dikonfigurasi (Token Fonnte kosong/default). Hubungi Koordinator.',
                ]);
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => $target,
                    'message' => $message,
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . $token
                ),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                \Log::error('OTP WhatsApp Error: ' . $err);
                return response()->json(['success' => false, 'message' => 'Gagal mengirim WhatsApp OTP. Error: ' . $err]);
            }

            // Cek respon API Fonnte
            $resData = json_decode($response, true);
            if (isset($resData['status']) && $resData['status'] === false) {
                $reason = $resData['reason'] ?? 'Unknown error';
                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal dari Fonnte: ' . $reason 
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'masked_target' => $maskedTarget,
            'message' => 'Kode OTP telah dikirim ke ' . $maskedTarget,
        ]);
    }

    /**
     * Step 2: Verify OTP code
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        $record = DB::table('password_resets_otp')
            ->where('username', $request->username)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak ditemukan. Silakan kirim ulang.',
            ]);
        }

        if (now()->greaterThan($record->expires_at)) {
            DB::table('password_resets_otp')->where('username', $request->username)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang.',
            ]);
        }

        if (!Hash::check($request->otp, $record->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP terverifikasi. Silakan buat password baru.',
        ]);
    }

    /**
     * Step 3: Reset password after OTP verification
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'required|string|size:6',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'new_password.min' => 'Password minimal 6 karakter.',
        ]);

        // Re-verify OTP one more time
        $record = DB::table('password_resets_otp')
            ->where('username', $request->username)
            ->first();

        if (!$record || now()->greaterThan($record->expires_at) || !Hash::check($request->otp, $record->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi OTP tidak valid. Silakan mulai ulang.',
            ]);
        }

        // Update password
        DB::table('users')
            ->where('username', $request->username)
            ->update(['password' => Hash::make($request->new_password)]);

        // Clean up OTP
        DB::table('password_resets_otp')->where('username', $request->username)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login dengan password baru.',
        ]);
    }

    public function showForceResetPassword()
    {
        $username = session('force_reset_username');
        if (!$username) {
            return redirect()->route('login')->with('error', 'Akses ditolak. Silakan login terlebih dahulu.');
        }
        return view('auth.force-reset-password', compact('username'));
    }

    public function forceResetPassword(Request $request)
    {
        $username = session('force_reset_username');
        $guard = session('force_reset_guard');
        if (!$username || !$guard) {
            return redirect()->route('login')->with('error', 'Sesi telah berakhir.');
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        DB::table('users')
            ->where('username', $username)
            ->update([
                'password' => Hash::make($request->password),
                'needs_password_reset' => 0
            ]);

        session()->forget(['force_reset_username', 'force_reset_guard']);

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui. Silakan login dengan password baru Anda.');
    }
}
