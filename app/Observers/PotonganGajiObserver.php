<?php

namespace App\Observers;

use App\Models\PotonganGaji;

class PotonganGajiObserver
{
    /**
     * Handle the PotonganGaji "created" event.
     */
    public function created(PotonganGaji $potonganGaji): void
    {
        activity_log('Menambahkan', 'Potongan Gaji', "Menambahkan data Potongan Gaji {$potonganGaji->nama_potongan}");
    }

    /**
     * Handle the PotonganGaji "updated" event.
     */
    public function updated(PotonganGaji $potonganGaji): void
    {
        $changes = $potonganGaji->getDirty();
        $original = $potonganGaji->getOriginal();

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
            $description = "Memperbarui data potongan gaji {$potonganGaji->nama_potongan}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Potongan Gaji', $description);
        }
    }

    /**
     * Handle the PotonganGaji "deleted" event.
     */
    public function deleted(PotonganGaji $potonganGaji): void
    {
        $original = $potonganGaji->getOriginal();
        $nama_potongan = $original['nama_potongan'];

        activity_log('Menghapus', 'Potongan Gaji', "Menghapus akun Potongan Gaji {$nama_potongan}");
    }
}
