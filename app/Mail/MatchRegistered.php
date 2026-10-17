<?php

namespace App\Mail;

use App\Models\GameMatch;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MatchRegistered extends Mailable
{
    use SerializesModels;

    public function __construct(public GameMatch $match)
    {
        $this->match->loadMissing(['ganador', 'perdedor']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Match registrado: {$this->match->ganador->nombre} vs {$this->match->perdedor->nombre}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.match-registered');
    }
}
