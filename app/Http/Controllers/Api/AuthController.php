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
        // Cek kredensial
        // -----------------------------
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        $user = User::with(['peran.fitur', 'departemen', 'jabatan'])
                    ->where('email', $request->email)
                    ->first();

        Log::info('User ditemukan', ['id' => $user->id]);

        // -----------------------------
        // Cek akses browser untuk non-superadmin
        // -----------------------------
        if ($user->peran->nama_peran !== 'Super Admin') {
            // Jika User-Agent mengindikasikan browser → blok login
            $userAgent = $request->header('User-Agent', '');
            if (str_contains($userAgent, 'Mozilla') || str_contains($userAgent, 'Chrome') || str_contains($userAgent, 'Safari') || str_contains($userAgent, 'Firefox')) {
                return response()->json([
                    'message' => 'Login via browser/web tidak diperbolehkan kecuali Super Admin.'
                ], 403);
            }
        }

        // -----------------------------
        // Validasi device untuk non-superadmin
        // -----------------------------
        if ($user->peran->nama_peran !== 'Super Admin') {
            $request->validate([
                'device_id'           => 'required|string',
                // 'device_model'        => 'required|string',
                // 'device_manufacturer' => 'required|string',
                // 'device_version'      => 'nullable|string',
            ]);

            $existingDevice = Device::where('device_id', $request->device_id)->first();
            if ($existingDevice && $existingDevice->user_id != $user->id) {
                return response()->json([
                    'message' => 'Device ini sudah terhubung ke akun lain. Silakan hubungi admin.'
                ], 403);
            }

            $device = $user->device()->first();

            if (!$device) {
                $user->device()->create([
                    'device_id'           => $request->device_id,
                    'device_model'        => $request->device_model,
                    'device_manufacturer' => $request->device_manufacturer,
                    'device_version'      => $request->device_version,
                    'last_login'          => now()
                ]);
            } elseif ($device->device_id !== $request->device_id) {
                return response()->json([
                    'message' => 'Akun anda sudah terhubung dengan device lain. Silakan gunakan device pertama atau hubungi admin.'
                ], 403);
            } else {
                $device->update(['last_login' => now()]);
            }
        }

        // -----------------------------
        // Buat token login
        // -----------------------------
        $token = $user->createToken('token_login')->plainTextToken;

        // Catat log login
        activity_log('Login', 'User', "{$user->nama} ({$user->email}) berhasil login");

        // -----------------------------
        // Onboarding
        // -----------------------------
        $onboarding = false;
        if (!$user->onboarding || $user->onboarding == "false" || $user->onboarding == 0) {
            $onboarding = true;
            $user->update(['onboarding' => 1]);
            $user = $user->fresh();
        }

        return response()->json([
            'message'    => 'Login berhasil',
            'token'      => $token,
            'data'       => $user,
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

    public function logout(Request $request)
    {
        $user = $request->user();

        // Catat log logout
        activity_log('Logout', 'User', "{$user->nama} ({$user->email}) berhasil logout");

        // Hapus token
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }
}
