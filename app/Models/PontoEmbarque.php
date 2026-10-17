<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ponto fixo de embarque com a sua geocerca circular.
 */
class PontoEmbarque extends Model
{
    use HasFactory;

    protected $table = 'pontos_embarque';

    protected $fillable = [
        'nome',
        'latitude',
        'longitude',
        'raio_metros',
        'ativo',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'raio_metros' => 'integer',
        'ativo' => 'boolean',
    ];

    public function linhas()
    {
        return $this->hasMany(LinhaPonto::class, 'ponto_id');
    }
}
