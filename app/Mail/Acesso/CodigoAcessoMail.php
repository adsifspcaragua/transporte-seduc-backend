<?php

namespace App\Mail\Acesso;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Codigo de acesso a area do estudante. Criptografado na fila, para o codigo
 * nao ficar legivel na tabela de jobs.
 */
class CodigoAcessoMail extends Mailable implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $codigo,
        public int $validadeMinutos,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu código de acesso ao transporte universitário',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.acesso.codigo',
        );
    }
}
