<?php

namespace App\Observers;

use App\Models\Gaji;

class GajiObserver
{
    /**
     * Handle the Gaji "updated" event.
     */
    public function updated(Gaji $gaji): void
    {
        $changes = $gaji->getChanges();
        $original = $gaji->getOriginal();

        $ignore = ['updated_at', 'created_at'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) {
                continue;
            }

            $oldValue = $original[$field];
            if ($oldValue == $newValue) {
                continue;
            }

            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if (!empty($detailChanges)) {
            $description = "Memperbarui data gaji {$gaji->user->nama}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Gaji', $description);
        }
    }

    /**
     * Handle the Gaji "deleted" event.
     */
    public function deleted(Gaji $gaji): void
    {
        // nanti bisa ditambahkan
    }
}
