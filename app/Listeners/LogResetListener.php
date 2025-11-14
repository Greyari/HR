<?php

namespace App\Listeners;

use App\Events\DataResetEvent;
use App\Models\LogAktivitas;

class LogResetListener
{
    public function handle(DataResetEvent $event)
    {
        LogAktivitas::create([
            'user_id'    => $event->user_id,
            'action'     => "Reset data {$event->module}",
            'description'=> "Menghapus {$event->jumlah} data pada bulan {$event->bulan} tahun {$event->tahun}",
        ]);
    }
}
