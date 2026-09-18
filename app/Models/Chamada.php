<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A folha de presenca de uma linha em um dia.
 */
class Chamada extends Model
{
    use HasFactory;

    public const ABERTA = 'Aberta';

    public const FECHADA = 'Fechada';

    protected $table = 'chamadas';

    protected $fillable = [
        'linha_id',
        'data',
        'status',
        'registrada_por',
        'fechada_em',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'fechada_em' => 'datetime',
    ];

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function frequencias()
    {
        return $this->hasMany(Frequencia::class, 'chamada_id');
    }

    public function registradaPor()
    {
        return $this->belongsTo(User::class, 'registrada_por');
    }

    public function estaAberta(): bool
    {
        return $this->status === self::ABERTA;
    }
}
