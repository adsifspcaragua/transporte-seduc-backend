<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentacaoReecadastro extends Model
{
    protected $table = 'documentacoes_reecadastro';

    protected $fillable = [
        'estudante_id',
        'type',
        'file_path',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }
}
