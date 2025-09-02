<?php

namespace App\Observers;

use App\Models\Tugas;


// noted harusnya nenti bisa munculin log mislanya user upload lampiran tugasnya
class TugasObserver
{
    /**
     * Handle the Tugas "created" event.
     */
    public function created(Tugas $tugas): void
    {
        activity_log('Menambahkan', 'Tugas', "Menambahkan data tugas {$tugas->nama_tugas}");
    }

    /**
     * Handle the Tugas "updated" event.
     */
    public function updated(Tugas $tugas): void
    {
        $changes = $tugas->getDirty();
        $original = $tugas->getOriginal();

        $ignore = ['updated_at', 'created_at'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) {
                continue;
            }

            $oldValue = $original[$field] ?? null;

            // Kalau field lampiran berubah
            if ($field === 'lampiran') {
                activity_log(
                    'Upload',
                    'Tugas',
                    "{$tugas->user->nama} mengupload lampiran untuk tugas {$tugas->nama_tugas}"
                );
                continue;
            }

            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if ($detailChanges) {
            $description = "Memperbarui data tugas {$tugas->nama_tugas}. Perubahan: " . implode(', ', $detailChanges);
            activity_log('Mengubah', 'Tugas', $description);
        }
    }

    /**
     * Handle the Tugas "deleted" event.
     */
    public function deleted(Tugas $tugas): void
    {
        $original = $tugas->getOriginal();
        $nama_tugas = $original['nama_tugas'];

        activity_log('Menghapus', 'Tugas', "Menghapus data tugas {$nama_tugas}");
    }
}
