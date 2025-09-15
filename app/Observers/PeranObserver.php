<?php

namespace App\Observers;

use App\Models\Peran;

class PeranObserver
{
    public function created(Peran $peran): void
    {
        activity_log('Menambahkan', 'Peran', "{$peran->nama_peran}  melakukan penambahan peran");
    }

    public function updated(Peran $peran): void
    {
        $changes = $peran->getDirty();
        $original = $peran->getOriginal();

        $ignore = ['updated_at', 'created_at', 'last_notified_at'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) {
                continue;
            }
            $oldValue = $original[$field];
            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if ($detailChanges) {
            $description = "Memperbarui data peran {$peran->nama_peran}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Peran', $description);
        }
    }

    public function deleted(Peran $peran): void
    {
        $original = $peran->getOriginal();
        $nama_peran = $original['nama_peran'];

        activity_log('Menghapus', 'Peran', "Menghapus data peran {$nama_peran}");
    }
}
