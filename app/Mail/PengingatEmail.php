<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        return $this->subject('Reminder System HRIS')
                    ->view('emails.pengingat');
    }
}
