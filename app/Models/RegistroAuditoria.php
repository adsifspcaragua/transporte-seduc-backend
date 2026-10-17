<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Uma acao administrativa sensivel: quem, o que, onde e por que.
 */
class RegistroAuditoria extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'registros_auditoria';

    protected $fillable = [
        'user_id',
        'acao',
        'auditavel_type',
        'auditavel_id',
        'dados',
        'justificativa',
    ];

    protected $casts = [
        'dados' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditavel()
    {
        return $this->morphTo();
    }
}
