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
 * Comunica ao estudante o resultado da analise da inscricao.
 */
class InscricaoAnalisadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Inscricao $inscricao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscrição analisada',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.inscricao.analisada',
            with: [
                'status' => match ($this->inscricao->status) {
                    'Em analise' => 'Em análise',
                    'Pendencia' => 'Pendência',
                    // A devolucao volta a inscricao para Incompleto.
                    'Incompleto' => 'Devolvida para correção',
                    default => $this->inscricao->status,
                },
                'camposPendentes' => array_map(
                    fn (string $campo) => Inscricao::CAMPOS_CORRIGIVEIS[$campo] ?? $campo,
                    $this->inscricao->campos_pendentes ?? [],
                ),
            ],
        );
    }
}
