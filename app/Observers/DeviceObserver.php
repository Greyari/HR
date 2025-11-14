<?php

namespace App\Observers;

use App\Models\Device;

class DeviceObserver
{
    /**
     * Event ketika device baru dibuat
     */
    // public function created(Device $device)
    // {
    //     if (app()->runningInConsole()) return;

    //     $nama = $device->user->nama ?? 'Tidak diketahui';

    //     activity_log(
    //         'Tambah Device',
    //         'Device',
    //         "Device baru terdaftar untuk user {$nama} ({$device->device_model})"
    //     );
    // }

    /**
     * Event ketika device diupdate (misal ganti device_hash / model)
     */
    public function updated(Device $device)
    {
        if (app()->runningInConsole()) return;

        $changes = $device->getDirty();
        $original = $device->getOriginal();

        $ignore = ['updated_at', 'created_at', 'user_id'];

        $detailChanges = [];
        foreach ($changes as $field => $newValue) {
            if (in_array($field, $ignore)) continue;
            $oldValue = $original[$field] ?? null;
            $detailChanges[] = "{$field}: '{$oldValue}' → '{$newValue}'";
        }

        if ($detailChanges) {
            $nama = $device->user->nama ?? 'Tidak diketahui';
            $desc = implode(', ', $detailChanges);

            activity_log(
                'Update Device',
                'Device',
                "Device milik {$nama} berubah. {$desc}"
            );
        }
    }

    /**
     * Event saat device dihapus
     */
    public function deleted(Device $device)
    {
        if (app()->runningInConsole()) return;

        $nama = $device->user->nama ?? 'Tidak diketahui';

        activity_log(
            'Reset Device',
            'Device',
            "Device milik {$nama} dihapus / direset oleh admin"
        );
    }
}
