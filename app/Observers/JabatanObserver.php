<?php

namespace App\Observers;

use App\Models\Jabatan;

class JabatanObserver
{
    /**
     * Handle the Jabatan "created" event.
     */
    public function created(Jabatan $jabatan): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        activity_log('Menambahkan', 'Jabatan', "Menambahkan data jabatan {$jabatan->nama_jabatan}");
    }

    /**
     * Handle the Jabatan "updated" event.
     */
    public function updated(Jabatan $jabatan): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        $changes = $jabatan->getDirty();
        $original = $jabatan->getOriginal();

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
            $description = "Memperbarui data jabatan {$jabatan->nama_jabatan}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Jabatan', $description);
        }
    }

    /**
     * Handle the Jabatan "deleted" event.
     */
    public function deleted(Jabatan $jabatan): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        $original = $jabatan->getOriginal();
        $nama_jabatan = $original['nama_jabatan'];

        activity_log('Menghapus', 'Jabatan', "Menghapus data jabatan {$nama_jabatan}");
    }
}
