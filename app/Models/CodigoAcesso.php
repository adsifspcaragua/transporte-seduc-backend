<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Codigo de uso unico enviado ao e-mail do cadastro no acesso por CPF.
 */
class CodigoAcesso extends Model
{
    use MassPrunable;

    protected $table = 'codigos_acesso';

    protected $fillable = [
        'cpf',
        'codigo_hash',
        'tentativas',
        'expira_em',
        'usado_em',
    ];

    protected $hidden = [
        'codigo_hash',
    ];

    protected $casts = [
        'tentativas' => 'integer',
        'expira_em' => 'datetime',
        'usado_em' => 'datetime',
    ];

    /** Passada a contagem de envios por hora, nao servem para nada. */
    public function prunable(): Builder
    {
        return static::where('expira_em', '<', now()->subDay());
    }
}
