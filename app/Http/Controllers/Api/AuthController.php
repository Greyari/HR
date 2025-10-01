<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;

class AuthController extends Controller
{
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
        // Cari user
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
        // Reset percobaan kalau berhasil login
        // -----------------------------
        if ($user->coba_login > 0) {
            $user->update(['coba_login' => 0]);
            $user->refresh();
        }

        // -----------------------------
        // Deteksi device menggunakan Agent
        // -----------------------------
        $agent = new Agent();

        $fiturUser = $user->peran->fitur->pluck('nama_fitur')->toArray();

        Log::info('Deteksi akses', [
            'userAgent' => $request->header('User-Agent'),
            'isDesktop' => $agent->isDesktop(),
            'isMobile'  => $agent->isMobile(),
            'fiturUser' => $fiturUser
        ]);

        // -----------------------------
        // Deteksi platform (web / apk)
        // -----------------------------
        $platform = $request->input('platform'); // dari request

        if (!$platform) {
            // fallback ke Agent kalau frontend tidak kirim platform
            if ($agent->isDesktop()) {
                $platform = 'web';
            } elseif ($agent->isMobile()) {
                $platform = 'apk';
            }
        }

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
        // Validasi device (hanya untuk akses mobile / apk)
        // -----------------------------
        if ($platform === 'apk') {
            $request->validate([
                'device_id'           => 'required|string',
                'device_model'        => 'nullable|string',
                'device_manufacturer' => 'nullable|string',
                'device_version'      => 'nullable|string',
            ]);

            // cek apakah user juga punya akses web
            $punyaWeb = in_array('web', $fiturUser);

            if (!$punyaWeb) {
                // device restriction + pencatatan device hanya untuk apk-only
                $existingDevice = Device::where('device_id', $request->device_id)->first();
                if ($existingDevice && $existingDevice->user_id != $user->id) {
                    return response()->json([
                        'message' => 'Device ini sudah terhubung ke akun lain. Silakan hubungi admin.'
                    ], 403);
                }

                $device = $user->device()->first();

                if (!$device) {
                    try {
                        $user->device()->create([
                            'device_id'           => $request->device_id,
                            'device_model'        => $request->device_model,
                            'device_manufacturer' => $request->device_manufacturer,
                            'device_version'      => $request->device_version,
                            'last_login'          => now()
                        ]);

                        Log::info('Device baru berhasil dibuat untuk apk-only user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('Gagal insert device', ['error' => $e->getMessage()]);
                        return response()->json([
                            'message' => 'Gagal menyimpan device. Silakan hubungi admin.'
                        ], 500);
                    }
                } else {
                    $device->update(['last_login' => now()]);
                    Log::info('Device terakhir login diperbarui (apk-only user)', ['device_id' => $device->device_id]);
                }
            } else {
                // kalau punya akses web+apk → skip pencatatan device
                Log::info('User punya akses web+apk, skip pencatatan device', ['user_id' => $user->id]);
            }
        }

        // -----------------------------
        // Buat token login
        // -----------------------------
        $token = $user->createToken('token_login')->plainTextToken;

        // Catat log login
        activity_log(
            'Login',
            'User',
            "{$user->nama} ({$user->email}) berhasil login",
            $user->id
        );

        // -----------------------------
        // Onboarding
        // -----------------------------
        $onboarding = false;

        if (!$user->onboarding || $user->onboarding == "false" || $user->onboarding == 0) {
            $onboarding = true;
            $user->update(['onboarding' => 1]);
            $user->load(['peran.fitur', 'departemen', 'jabatan']);
        }

        return response()->json([
            'message'    => 'Login berhasil',
            'token'      => $token,
            'data'       => $user->setAttribute('gaji_per_hari', (int) $user->gaji_per_hari),
            'onboarding' => $onboarding,
        ]);
    }

    // ganti email
    public function updateEmail(Request $request)
    {
        // Dapatkan pengguna yang sedang login
        // $user = Auth::user();
        $user = User::find(Auth::id());

        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password salah',
            ], 422);
        }

        // Update email pengguna
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'message' => 'Email berhasil diperbarui',
            'data' => ['email' => $user->email]
        ], 200);
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

    // Ganti password
    public function changePassword(Request $request)
    {
        $user = $request->user();

        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah',
            ], 422);
        }

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diperbarui'
        ], 200);
    }

    // Fitur logout
    public function logout(Request $request)
    {
        Log::info('Masuk ke logout endpoint', [
            'auth_user' => $request->user(),
            'headers' => $request->headers->all()
        ]);

        $user = $request->user();
        if ($user) {
            activity_log(
                'Logout',
                'User',
                "{$user->nama} ({$user->email}) berhasil logout",
                $user->id
            );

            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logout berhasil']);
    }
}
