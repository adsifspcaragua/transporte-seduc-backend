<?php

namespace App\Mail\Recadastro;
use Illuminate\Mail\Mailable;

class ReacadastroAnalisado extends Mailable
{
    public function __construct(
        public array $dados,
    ) {}

    public function build()
    {
        return $this
            ->subject('Recadastro analisado com sucesso')
            ->view('emails.recadastro-analisado');
    }
}