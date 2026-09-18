<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Linhas de idioma da redefinição de senha
    |--------------------------------------------------------------------------
    |
    | Retornos do fluxo "esqueci minha senha". O PasswordResetService nao revela
    | se o e-mail existe, entao `user` praticamente nao aparece na API.
    |
    */

    'reset' => 'Sua senha foi redefinida.',
    'sent' => 'Enviamos o link de redefinição de senha para o seu e-mail.',
    'throttled' => 'Aguarde um momento antes de tentar novamente.',
    'token' => 'Este token de redefinição de senha é inválido ou expirou.',
    'user' => 'Não encontramos nenhum usuário com esse e-mail.',

];
