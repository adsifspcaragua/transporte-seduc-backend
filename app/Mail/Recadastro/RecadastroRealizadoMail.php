<?php

namespace App\Mail\Recadastro;

use App\Models\SolicitacaoReecadastro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirma ao estudante que o recadastro foi recebido e aguarda analise.
 */
class RecadastroRealizadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SolicitacaoReecadastro $solicitacao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recadastro realizado com sucesso',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.recadastro.realizado',
            with: [
                'estudante' => $this->solicitacao->estudante,
            ],
        );
    }
}
