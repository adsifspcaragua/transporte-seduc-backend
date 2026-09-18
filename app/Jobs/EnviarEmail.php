<?php

namespace App\Jobs;

use App\Mail\Inscricao\InscricaoAnalisada;
use App\Mail\Inscricao\InscricaoRealizada;
use App\Mail\Recadastro\RecadastroAnalisado;
use App\Mail\Recadastro\RecadastroRealizado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarEmail implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $email,
        public string $tipo,
        public array $dados = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         $mailable = match ($this->tipo) {
            'inscricao-realizada' => new InscricaoRealizada($this->dados),
            'inscricao-analisada' => new InscricaoAnalisada($this->dados),
            'recadastro-realizado' => new RecadastroRealizado($this->dados),
            'recadastro-analisado' => new RecadastroAnalisado($this->dados),

            default => throw new \InvalidArgumentException(
                "Tipo de e-mail inválido: {$this->tipo}"
            ),
        };

        try {
            
            Mail::to($this->email)->send($mailable);
            dd('Email enviado com sucesso para: ' . $this->email); 
            
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
            }
}
