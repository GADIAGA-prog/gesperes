<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DigestAlertesRh extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $retraites,
        public int $docsExpires,
        public int $docsBientot,
        public Collection $recents,
    ) {}

    public function build(): self
    {
        return $this->subject('Alertes RH — ' . now()->format('d/m/Y'))
            ->view('emails.alertes-digest');
    }
}
