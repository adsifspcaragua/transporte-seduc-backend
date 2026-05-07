<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitacaoReecadastro extends Model
{
    protected $table = 'solicitacoes_reecadastro';
    protected $fillable = [
        'estudante_id',
        'status',
        'observacoes'
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    
}
