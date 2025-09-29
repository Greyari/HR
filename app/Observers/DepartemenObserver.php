<?php

namespace App\Observers;

use App\Models\Departemen;

class DepartemenObserver
{
    /**
     * Handle the Departemen "created" event.
     */
    public function created(Departemen $departemen): void
    {
        if (app()->runningInConsole()) {
            return; // skip log kalau sedang seeding/migrate
        }

        activity_log('Menambahkan', 'Departemen', "Menambahkan data departemen {$departemen->nama_departemen}");
    }

    /**
     * Handle the Departemen "updated" event.
     */
    public function updated(Departemen $departemen): void
    {
        if (app()->runningInConsole()) {
            return; // skip log kalau sedang seeding/migrate
        }

        $changes = $departemen->getDirty();
        $original = $departemen->getOriginal();

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
            $description = "Memperbarui data departemen {$departemen->nama_departemen}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Departemen', $description);
        }
    }

    /**
     * Handle the Departemen "deleted" event.
     */
    public function deleted(Departemen $departemen): void
    {
        if (app()->runningInConsole()) {
            return; // skip log kalau sedang seeding/migrate
        }

        $original = $departemen->getOriginal();
        $nama_departemen = $original['nama_departemen'];

        activity_log('Menghapus', 'Departemen', "Menghapus data departemen {$nama_departemen}");
    }
}
