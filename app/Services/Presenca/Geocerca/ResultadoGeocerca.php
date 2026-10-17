<?php

namespace App\Services\Presenca\Geocerca;

use App\Enums\CodigoPresenca;

final class ResultadoGeocerca
{
    public const DENTRO = 'dentro';

    public const FORA = 'fora';

    /** Dentro do raio, mas fora do horario previsto do ponto. */
    public const FORA_DO_HORARIO = 'fora_do_horario';

    /** A linha nao tem area cadastrada: nao ha como aprovar sozinho. */
    public const SEM_GEOCERCA = 'sem_geocerca';

    private function __construct(
        public readonly string $resultado,
        public readonly ?CodigoPresenca $codigo,
        public readonly ?int $pontoId = null,
        public readonly ?int $distanciaMetros = null,
    ) {}

    public static function dentro(?int $pontoId, ?int $distanciaMetros): self
    {
        return new self(self::DENTRO, null, $pontoId, $distanciaMetros);
    }

    public static function fora(?int $pontoId, ?int $distanciaMetros): self
    {
        return new self(self::FORA, CodigoPresenca::OUTSIDE_GEOFENCE, $pontoId, $distanciaMetros);
    }

    public static function foraDoHorario(?int $pontoId, ?int $distanciaMetros): self
    {
        return new self(self::FORA_DO_HORARIO, CodigoPresenca::OUTSIDE_TIME_WINDOW, $pontoId, $distanciaMetros);
    }

    public static function semGeocerca(): self
    {
        return new self(self::SEM_GEOCERCA, CodigoPresenca::MANUAL_REVIEW_REQUIRED);
    }

    public function aceita(): bool
    {
        return $this->resultado === self::DENTRO;
    }
}
