<?php

namespace App\Observers;

use App\Models\Kantor;

class KantorObserver
{
    /**
     * Handle the Kantor "created" event.
     */
    public function created(Kantor $kantor): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        activity_log('Menambahkan', 'Kantor', "Menambahkan data kantor");
    }

    /**
     * Handle the Kantor "updated" event.
     */
    public function updated(Kantor $kantor): void
    {
        if (app()->runningInConsole()) {
            return; // skip log saat seeding/migrate
        }

        $changes = $kantor->getDirty();
        $original = $kantor->getOriginal();

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
            $description = "Memperbarui data kantor. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Kantor', $description);
        }
    }
}
