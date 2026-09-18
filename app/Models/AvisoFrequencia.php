<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro do que a regra de frequencia fez com o estudante no mes.
 */
class AvisoFrequencia extends Model
{
    /** Uma falta de chegar ao limite de faltas no mes. */
    public const PERTO_DO_LIMITE_NO_MES = 'Perto do limite no mes';

    /** Uma falta de chegar ao limite de faltas seguidas. */
    public const PERTO_DO_LIMITE_SEGUIDAS = 'Perto do limite de seguidas';

    public const BENEFICIO_PERDIDO = 'Beneficio perdido';

    protected $table = 'avisos_frequencia';

    protected $fillable = [
        'estudante_id',
        'referencia',
        'tipo',
        'faltas_no_mes',
        'faltas_seguidas',
        'email',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }
}
