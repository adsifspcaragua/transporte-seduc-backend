<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscricaoDocumento extends Model
{
    protected $table = "inscricaos";
    protected $fillable = [ 
        'type',
        'file_path',
        'inscricao_id'
    ];

    public function inscricao(){
        return $this->belongsTo(Inscricao::class);
    }
}
