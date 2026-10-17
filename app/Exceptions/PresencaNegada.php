<?php

namespace App\Exceptions;

use App\Enums\CodigoPresenca;
use App\Models\Viagem;
use RuntimeException;

/**
 * Uma das verificacoes da presenca recusou a tentativa.
 *
 * Circula so dentro dos services do fluxo: quem a captura registra a
 * tentativa e devolve a resposta com o codigo. A viagem vem quando ja foi
 * identificada, para o estudante poder pedir revisao dela.
 */
class PresencaNegada extends RuntimeException
{
    /**
     * @param  array{distancia_metros?: int|null, precisao_metros?: int|null}  $contexto
     */
    public function __construct(
        public readonly CodigoPresenca $codigo,
        public readonly ?Viagem $viagem = null,
        public readonly array $contexto = [],
    ) {
        parent::__construct($codigo->value);
    }
}
