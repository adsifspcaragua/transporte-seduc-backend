<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodoReecadastro extends Model
{
    use HasFactory;

    protected $table = 'periodos_reecadastro';

    protected $fillable = [
        'ano',
        'semestre',
        'data_inicio',
        'data_fim',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function solicitacoes()
    {
        return $this->hasMany(SolicitacaoReecadastro::class, 'periodo_id');
    }

    /**
     * Período atualmente liberado para os estudantes recadastrarem.
     * Só existe um por vez (garantido pelo PeriodoReecadastroService).
     */
    public static function aberto(): ?self
    {
        return static::where('status', 'Aberto')->latest('data_inicio')->first();
    }

    /** Rótulo do período no formato usado pela secretaria (ex.: 2026/1). */
    public function getReferenciaAttribute(): string
    {
        return $this->ano.'/'.$this->semestre;
    }
}
