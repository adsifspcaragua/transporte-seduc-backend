<?php

namespace App\Mail\Frequencia;

use App\Models\Estudante;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Comunica ao estudante que ele perdeu o beneficio pelas faltas do mes.
 *
 * Cortar o transporte sem avisar deixaria o estudante descobrir no ponto de
 * onibus, quando o motorista nao o encontra mais na lista.
 */
class BeneficioPerdidoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $situacao  retorno de BeneficioFrequenciaService::situacao()
     */
    public function __construct(
        public Estudante $estudante,
        public array $situacao,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu benefício do transporte universitário foi suspenso',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.frequencia.beneficio-perdido',
            with: [
                'mes' => NomeDoMes::de($this->situacao['referencia']),
                'porSeguidas' => $this->situacao['maior_sequencia'] >= $this->situacao['limite_seguidas'],
                'datas' => array_map(fn (string $data) => Carbon::parse($data)->format('d/m/Y'), $this->situacao['datas_das_faltas']),
            ],
        );
    }
}
