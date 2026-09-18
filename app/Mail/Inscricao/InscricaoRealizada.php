<?php

namespace App\Mail\Inscricao;

use Illuminate\Mail\Mailable;

class InscricaoRealizada extends Mailable
{
    public function __construct(
        public array $dados,
    ) {}

    public function build()
    {
        return $this
            ->subject('Inscrição realizada com sucesso')
            ->view('emails.inscricao-realizada');
    }
}