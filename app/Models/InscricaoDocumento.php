<?php

namespace App\Models;

use App\Models\Inscricao;
use Illuminate\Database\Eloquent\Model;

class InscricaoDocumento extends Model
{
    protected $fillable = [
        'inscricao_id',
        'type',
        'file_path'
    ];

    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

  
}
