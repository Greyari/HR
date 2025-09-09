<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PengingatEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pengingat;

    public function __construct($pengingat)
    {
        $this->pengingat = $pengingat;
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject('Reminder System HRIS')
                    ->view('emails.pengingat');
    }

    /**
     * Akan otomatis dipanggil kalau job ini gagal dijalankan oleh queue worker
     */
    public function failed(\Throwable $exception)
    {
        Log::channel('scheduler')->error('[Job Failed] PengingatEmail gagal dikirim: ' . $exception->getMessage(), [
            'pengingat_id' => $this->pengingat->id ?? null,
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
