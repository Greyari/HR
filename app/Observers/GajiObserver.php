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
            if (in_array($field, $ignore)) continue;

            $oldValue = $original[$field];

            // Jika array, ubah jadi string
            if (is_array($oldValue)) {
                $oldValue = json_encode($oldValue);
            }
            if (is_array($newValue)) {
                $newValue = json_encode($newValue);
            }

            if ($oldValue == $newValue) continue;

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
