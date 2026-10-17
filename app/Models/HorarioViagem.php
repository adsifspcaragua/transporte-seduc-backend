<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A programacao de uma linha em um sentido: dias da semana e horario de saida.
 */
class HorarioViagem extends Model
{
    use HasFactory;

    public const IDA = 'Ida';

    public const VOLTA = 'Volta';

    public const SENTIDOS = [self::IDA, self::VOLTA];

    protected $table = 'horarios_viagem';

    protected $fillable = [
        'linha_id',
        'sentido',
        'dias_semana',
        'horario_saida',
        'janela_antes_min',
        'janela_depois_min',
        'vigencia_inicio',
        'vigencia_fim',
        'ativo',
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'janela_antes_min' => 'integer',
        'janela_depois_min' => 'integer',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'ativo' => 'boolean',
    ];

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function viagens()
    {
        return $this->hasMany(Viagem::class, 'horario_id');
    }

    /**
     * Se ha viagem deste horario no dia. Como em estudantes.days_of_week, o dia
     * pode ter sido guardado como numero ou como texto; os dois contam.
     */
    public function operaEm(Carbon $dia): bool
    {
        if (! $this->ativo) {
            return false;
        }

        // Compara as datas como texto (AAAA-MM-DD): o dia da operacao e o da
        // vigencia podem vir em fusos diferentes.
        $data = $dia->toDateString();

        if ($this->vigencia_inicio && $data < $this->vigencia_inicio->toDateString()) {
            return false;
        }

        if ($this->vigencia_fim && $data > $this->vigencia_fim->toDateString()) {
            return false;
        }

        return in_array($dia->dayOfWeek, array_map('intval', $this->dias_semana ?? []), true);
    }

    public function janelaAntes(): int
    {
        return $this->janela_antes_min ?? (int) config('presenca.janela.antes_minutos');
    }

    public function janelaDepois(): int
    {
        return $this->janela_depois_min ?? (int) config('presenca.janela.depois_minutos');
    }
}
