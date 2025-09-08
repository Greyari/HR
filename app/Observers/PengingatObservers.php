<?php

namespace App\Observers;

use App\Models\Pengingat;

class PengingatObservers
{
    public function created(Pengingat $pengingat): void
    {
        activity_log('Menambahkan', 'Pengingat', "{$pengingat->peran->nama_peran}  melakukan penambahan pengingat");

    }

    public function updated(Pengingat $pengingat): void
    {
        $changes = $pengingat->getDirty();
        $original = $pengingat->getOriginal();

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
            $description = "Memperbarui data pengingat {$pengingat->judul}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Pengingat', $description);
        }
    }

    public function deleted(Pengingat $pengingat): void
    {
        $original = $pengingat->getOriginal();
        $judul = $original['judul'];

        activity_log('Menghapus', 'Pengingat', "Menghapus data pengingat {$judul}");
    }
}
