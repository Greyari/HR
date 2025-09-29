<?php

namespace App\Observers;

use App\Models\User;

class KaryawanObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        activity_log('Menambahkan', 'Karyawan', "Menambahkan data karyawan {$user->nama}");
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        $changes = $user->getDirty();
        $original = $user->getOriginal();

        $ignore = ['updated_at', 'created_at'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) {
                continue;
            }
            $oldValue = $original[$field] ?? null;
            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if ($detailChanges) {
            $description = "Memperbarui data karyawan {$user->nama}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Karyawan', $description);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        $original = $user->getOriginal();
        $nama = $original['nama'];

        activity_log('Menghapus', 'Karyawan', "Menghapus akun karyawan {$nama}");
    }
}
