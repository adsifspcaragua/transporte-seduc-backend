<?php

namespace App\Mail\Recadastro;
use Illuminate\Mail\Mailable;

class ReacadastroRealisado extends Mailable
{
    public function __construct(
        public array $dados,
    ) {}

    public function build()
    {
        return $this
            ->subject('Recadastro realizado com sucesso')
            ->view('emails.recadastro-realizado');
    }
}