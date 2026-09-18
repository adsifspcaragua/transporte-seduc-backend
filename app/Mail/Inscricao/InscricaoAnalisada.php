<?php

namespace App\Mail\Inscricao;
use Illuminate\Mail\Mailable;

class InscricaoAnalisada extends Mailable
{
    public function __construct(
        public array $dados,
        
    ) {}

    public function build()
    {
        return $this
            ->subject('Inscrição analisada com sucesso')
            ->view('emails.inscricao-analisada');
    }
}