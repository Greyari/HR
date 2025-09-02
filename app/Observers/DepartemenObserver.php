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
        activity_log('Menambahkan', 'Departemen', "Menambahkan data departemen {$departemen->nama_departemen}");
    }

    /**
     * Handle the Departemen "updated" event.
     */
    public function updated(Departemen $departemen): void
    {
        $changes = $departemen->getDirty();
        $original = $departemen->getOriginal();

        $ignore = ['updated_at', 'created_at'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) {
                continue;
            }
            $oldValue = $original[$field];
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
        $original = $departemen->getOriginal();
        $nama_departemen = $original['nama_departemen'];

        activity_log('Menghapus', 'Departemen', "Menghapus data departemen {$nama_departemen}");
    }
}
