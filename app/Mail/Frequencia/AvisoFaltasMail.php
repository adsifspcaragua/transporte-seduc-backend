<?php

namespace App\Mail\Frequencia;

use App\Models\AvisoFrequencia;
use App\Models\Estudante;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Avisa o estudante de que uma nova falta no mes o tira do beneficio.
 */
class AvisoFaltasMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $situacao  retorno de BeneficioFrequenciaService::situacao()
     * @param  list<string>  $motivos  tipos de AvisoFrequencia que motivaram o aviso
     */
    public function __construct(
        public Estudante $estudante,
        public array $situacao,
        public array $motivos,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Atenção: suas faltas no transporte universitário',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.frequencia.aviso',
            with: [
                'mes' => NomeDoMes::de($this->situacao['referencia']),
                'pertoNoMes' => in_array(AvisoFrequencia::PERTO_DO_LIMITE_NO_MES, $this->motivos, true),
                'pertoSeguidas' => in_array(AvisoFrequencia::PERTO_DO_LIMITE_SEGUIDAS, $this->motivos, true),
                'datas' => array_map(fn (string $data) => Carbon::parse($data)->format('d/m/Y'), $this->situacao['datas_das_faltas']),
            ],
        );
    }
}
