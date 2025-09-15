<?php
namespace App\Observers;

use App\Models\User;

class AuthObserver
{
    // Method login manual
    public function login(User $user)
    {
        activity_log('Login', 'User', "{$user->nama} ({$user->email}) berhasil login");
    }

    // Method logout manual
    public function logout(User $user)
    {
        activity_log('Logout', 'User', "{$user->nama} ({$user->email}) berhasil logout");
    }

    // Method update email/password bisa pakai updated()
    public function updated(User $user): void
    {
        $changes = $user->getDirty();
        $original = $user->getOriginal();
        $ignore = ['updated_at', 'last_login'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) continue;
            $oldValue = $original[$field] ?? null;
            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if ($detailChanges) {
            activity_log('Mengubah', 'Profile', "Perubahan data profile {$user->nama}: " . implode(', ', $detailChanges));
        }
    }
}
