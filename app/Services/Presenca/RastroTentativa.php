<?php

namespace App\Services\Presenca;

use App\Models\DispositivoEstudante;
use App\Models\Veiculo;
use App\Models\Viagem;

/**
 * O que ja se sabia da tentativa quando ela parou. Vai para o registro da
 * tentativa, aceita ou negada, e ajuda o suporte a entender o que houve.
 */
final class RastroTentativa
{
    public ?DispositivoEstudante $dispositivo = null;

    public ?Veiculo $veiculo = null;

    public ?Viagem $viagem = null;

    public ?int $distanciaMetros = null;

    public ?int $precisaoMetros = null;
}
