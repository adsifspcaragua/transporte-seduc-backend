<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = "documentos";

    protected $fillable = [
        "estudante_id",
        "tipo",
        "arquivo_path"
    ];


    public function estudante(){
       return $this->belongsTo(Estudante::class, 'estudante_id');
    }
}
