<?php

namespace App\Listeners;

use App\Events\DataResetEvent;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;

class LogResetListener
{
    public function handle(DataResetEvent $event)
    {
        Log::info("=== DEBUG: LISTENER TERPANGGIL ===", [
            'module' => $event->module,
            'user_id' => $event->user_id,
        ]);

        ActivityLog::create([
            'user_id'     => $event->user_id,
            'action'      => 'Reset data ' . $event->module,
            'module'      => $event->module,
            'description' => 'Menghapus ' . $event->jumlah . ' data pada bulan ' . $event->bulan . ' tahun ' . $event->tahun,
            'ip_address'  => $event->ip,
            'user_agent'  => $event->agent,
        ]);
    }
}
