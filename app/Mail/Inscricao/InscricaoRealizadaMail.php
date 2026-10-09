<?php

namespace App\Mail\Inscricao;

use App\Models\Inscricao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirma ao estudante que a inscricao foi recebida e aguarda analise.
 */
class InscricaoRealizadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inscricao $inscricao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscrição realizada com sucesso',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inscricao.realizada',
        );
    }
}
