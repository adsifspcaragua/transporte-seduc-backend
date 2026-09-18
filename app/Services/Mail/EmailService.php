<?php

namespace App\Services\Mail;

use App\Jobs\EnviarEmail;
use App\Models\Inscricao;


class EmailService
{
    //$destinatario, $mailable
    public function enviar()
    {
        $estudante = Inscricao::select('name', 'email', 'observation', 'status')->where('id', 1)->first();
        

        EnviarEmail::dispatch($estudante->email, 'inscricao-realizada', [
            'nome' => $estudante->name,
            'observation' => $estudante->observation,
            'status' => $estudante->status,
        ]);
        //dd('Job de envio de e-mail despachado com sucesso para: ' . $estudante->email); // Apenas para fins de teste, remova ou comente esta linha quando for para produção
    }
}