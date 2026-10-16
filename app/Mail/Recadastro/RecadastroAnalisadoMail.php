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
 * Comunica ao estudante o resultado da analise do recadastro.
 */
class RecadastroAnalisadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SolicitacaoReecadastro $solicitacao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recadastro analisado',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.recadastro.analisado',
            with: [
                'estudante' => $this->solicitacao->estudante,
                'status' => match ($this->solicitacao->status) {
                    'Em analise' => 'Em análise',
                    'Pendencia' => 'Pendência',
                    default => $this->solicitacao->status,
                },
                'camposPendentes' => array_map(
                    fn (string $campo) => SolicitacaoReecadastro::CAMPOS_CORRIGIVEIS[$campo] ?? $campo,
                    $this->solicitacao->campos_pendentes ?? [],
                ),
            ],
        );
    }
}
