<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A presenca do estudante em uma viagem. No maximo uma por viagem.
 */
class Presenca extends Model
{
    /** QR da van + assinatura do dispositivo + horario + geocerca. */
    public const DISPOSITIVO = 'Dispositivo';

    /** Excecao administrativa, com justificativa e responsavel. */
    public const MANUAL = 'Manual';

    protected $table = 'presencas';

    protected $fillable = [
        'estudante_id',
        'viagem_id',
        'veiculo_id',
        'dispositivo_id',
        'ponto_id',
        'metodo',
        'registrada_em',
        'politica_geo',
        'resultado_geo',
        'distancia_metros',
        'precisao_metros',
        'registrada_por',
        'justificativa',
    ];

    protected $casts = [
        'registrada_em' => 'datetime',
        'distancia_metros' => 'integer',
        'precisao_metros' => 'integer',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function viagem()
    {
        return $this->belongsTo(Viagem::class, 'viagem_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function ponto()
    {
        return $this->belongsTo(PontoEmbarque::class, 'ponto_id');
    }

    public function registradaPor()
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }
}
