<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use App\Models\Pengaturan;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    private function getUserLanguage($userId)
    {
        return Pengaturan::where('user_id', $userId)->value('bahasa') ?? 'indonesia';
    }

    // Fitur login
    public function login(Request $request)
    {
        Log::info('Login request masuk', $request->all());

        // -----------------------------
        // Validasi input dasar
        // -----------------------------
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // -----------------------------
        // Cari user berdasarkan email
        // -----------------------------
        $user = User::with(['peran.fitur', 'departemen', 'jabatan'])
                    ->where('email', $request->email)
                    ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // -----------------------------
        // Cek apakah akun terkunci
        // -----------------------------
        if ($user->terkunci) {
            return response()->json([
                'message' => 'Akun terkunci setelah 3 kali percobaan. Hubungi admin.'
            ], 403);
        }

        // -----------------------------
        // Cek password
        // -----------------------------
        if (!Hash::check($request->password, $user->password)) {
            $user->increment('coba_login');

            if ($user->coba_login >= 3) {
                $user->update(['terkunci' => true]);
                return response()->json([
                    'message' => 'Akun terkunci setelah 3 kali percobaan login gagal. Hubungi admin.'
                ], 403);
            }

            return response()->json([
                'message' => 'Email atau password salah. Percobaan: ' . $user->coba_login . '/3'
            ], 401);
        }

        // -----------------------------
        // Reset percobaan login jika berhasil
        // -----------------------------
        if ($user->coba_login > 0) {
            $user->update(['coba_login' => 0]);
            $user->refresh();
        }

        // -----------------------------
        // Deteksi device (menggunakan Agent)
        // -----------------------------
        $agent = new Agent();
        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        $platform = $request->input('platform');
        if (!$platform) {
            if ($agent->isDesktop()) $platform = 'web';
            elseif ($agent->isMobile()) $platform = 'apk';
        }

        Log::info('Deteksi akses user', [
            'userAgent' => $request->header('User-Agent'),
            'platform'  => $platform,
            'fiturUser' => $fiturUser
        ]);

        // -----------------------------
        // Validasi akses berdasarkan fitur role
        // -----------------------------
        if ($platform === 'web' && !in_array('web', $fiturUser)) {
            return response()->json([
                'message' => 'Login via web tidak diperbolehkan untuk akun ini.'
            ], 403);
        }

        if ($platform === 'apk' && !in_array('apk', $fiturUser)) {
            return response()->json([
                'message' => 'Login via apk tidak diperbolehkan untuk akun ini.'
            ], 403);
        }

        // -----------------------------
        // Simpan / update device_token (untuk semua user)
        // -----------------------------
        if ($request->filled('device_token')) {
            $user->update(['device_token' => $request->device_token]);
            Log::info('Device token diperbarui', [
                'user_id'  => $user->id,
                'platform' => $platform
            ]);
        }

        // -----------------------------
        // Validasi device (APK)
        // -----------------------------
        if ($platform === 'apk') {
            $request->validate([
                'device_hash'         => 'required|string',
                'device_model'        => 'nullable|string',
                'device_manufacturer' => 'nullable|string',
                'device_version'      => 'nullable|string',
            ]);

            // cek apakah user juga punya akses web
            $punyaWeb = in_array('web', $fiturUser);

            if (!$punyaWeb) {
                // 🔍 Cek apakah device_hash sudah terdaftar di user lain
                $existing = Device::where('device_hash', $request->device_hash)
                                ->where('user_id', '!=', $user->id)
                                ->first();

                if ($existing) {
                    return response()->json([
                        'message' => 'Perangkat ini sudah terhubung ke akun lain. Silakan hubungi admin.'
                    ], 403);
                }

                // Simpan atau perbarui data device user ini
                $device = Device::updateOrCreate(
                    ['device_hash' => $request->device_hash],
                    [
                        'user_id'            => $user->id,
                        'device_id'          => $request->device_id,
                        'device_model'       => $request->device_model,
                        'device_manufacturer'=> $request->device_manufacturer,
                        'device_version'     => $request->device_version,
                    ]
                );

                Log::info('Device hash tercatat / diperbarui', [
                    'user_id' => $user->id,
                    'device_hash' => $request->device_hash,
                ]);
            } else {
                Log::info('User punya akses web+apk, skip pencatatan device', [
                    'user_id' => $user->id
                ]);
            }
        }

        // -----------------------------
        // Buat token login (Sanctum)
        // -----------------------------
        $token = $user->createToken('token_login')->plainTextToken;

        // -----------------------------
        // Catat log aktivitas login
        // -----------------------------
        activity_log(
            'Login',
            'User',
            "{$user->nama} ({$user->email}) berhasil login",
            $user->id
        );

        // -----------------------------
        // Response sukses
        // -----------------------------
        return response()->json([
            'message'    => 'Login berhasil',
            'token'      => $token,
            'data'       => $user->setAttribute('gaji_per_hari', (int) $user->gaji_per_hari),
        ]);
    }


    // Ambil data user dari token
    public function me(Request $request)
    {
        $user = $request->user()->load(['peran.fitur', 'departemen', 'jabatan']);

        return response()->json([
            'message' => 'User ditemukan',
            'data'    => $user,
        ]);
    }

    // Ganti email
    public function updateEmail(Request $request)
    {
        $user = User::find(Auth::id());
        $bahasa = $this->getUserLanguage($user->id);

        // === PESAN VALIDASI ===
        $messages = [
            'indonesia' => [
                'old_password.required' => 'Password lama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Email tidak valid.',
                'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
                'email.unique' => 'Email sudah digunakan.',
            ],
            'inggris' => [
                'old_password.required' => 'Old password is required.',
                'email.required' => 'Email is required.',
                'email.email' => 'Invalid email format.',
                'email.max' => 'Email must not exceed 255 characters.',
                'email.unique' => 'Email is already taken.',
            ]
        ];

        try {
            $validated = $request->validate([
                'old_password' => 'required|string',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ], $messages[$bahasa]);

        } catch (ValidationException $e) {
            $errorMessages = $e->errors();
            $firstError = collect($errorMessages)->flatten()->first();

            return response()->json([
                'message' => $firstError,
                'errors' => $errorMessages,
            ], 422);
        }

        // Cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Password lama salah.'
                    : 'Incorrect old password.',
            ], 422);
        }

        // Update email
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Email berhasil diperbarui.'
                : 'Email updated successfully.',
            'data' => ['email' => $user->email]
        ], 200);
    }


    // Ganti password
    public function changePassword(Request $request)
    {
        $user = $request->user();
        $bahasa = $this->getUserLanguage($user->id);

        // === PESAN VALIDASI ===
        $messages = [
            'indonesia' => [
                'old_password.required' => 'Password lama wajib diisi.',
                'new_password.required' => 'Password baru wajib diisi.',
                'new_password.min' => 'Password baru minimal 6 karakter.',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            ],
            'inggris' => [
                'old_password.required' => 'Old password is required.',
                'new_password.required' => 'New password is required.',
                'new_password.min' => 'New password must be at least 6 characters.',
                'new_password.confirmed' => 'Password confirmation does not match.',
            ]
        ];

        try {
            $validated = $request->validate([
                'old_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ], $messages[$bahasa]);

        } catch (ValidationException $e) {
            $errorMessages = $e->errors();
            $firstError = collect($errorMessages)->flatten()->first();

            return response()->json([
                'message' => $firstError,
                'errors' => $errorMessages,
            ], 422);
        }

        // Cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => $bahasa === 'indonesia'
                    ? 'Password lama salah.'
                    : 'Incorrect old password.',
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => $bahasa === 'indonesia'
                ? 'Password berhasil diperbarui.'
                : 'Password updated successfully.',
        ], 200);
    }


    // Fitur logout
    public function logout(Request $request)
    {
        Log::info('Masuk ke logout endpoint', [
            'Authorization' => $request->header('Authorization')
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                Log::warning('Logout gagal: user null atau token tidak valid');
                return response()->json(['message' => 'Token invalid atau expired'], 401);
            }

            Log::info('Logout user ditemukan', [
                'user_id' => $user->id,
                'device_token' => $user->device_token,
            ]);

            // Hapus token device agar tidak terima push notif lagi
            $user->update(['device_token' => null]);

            // Hapus semua personal access token (Sanctum)
            $request->user()->currentAccessToken()->delete();

            // Catat ke activity log
            activity_log('Logout', 'User', "{$user->nama} logout", $user->id);

            Log::info('Logout berhasil untuk user', ['user_id' => $user->id]);

            return response()->json(['message' => 'Logout berhasil'], 200);

        } catch (\Exception $e) {
            Log::error('Error di logout: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server saat logout'], 500);
        }
    }
}
