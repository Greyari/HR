<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DataResetEvent
{
    use Dispatchable;

    public $module;
    public $bulan;
    public $tahun;
    public $jumlah;
    public $user_id;

    public function __construct($module, $bulan, $tahun, $jumlah, $user_id)
    {
        $this->module  = $module;
        $this->bulan   = $bulan;
        $this->tahun   = $tahun;
        $this->jumlah  = $jumlah;
        $this->user_id = $user_id;
    }
}
