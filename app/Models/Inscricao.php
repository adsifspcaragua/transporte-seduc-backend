<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    protected $table = "inscricaos";
    protected $fillable = [ 
        'name',
        'cpf',
        'rg',
        'date_of_birth',
        'phone',
        'email',
        'cep',
        'address',
        'neighborhood',
        'city',
        'number',
        'status',
        'inscricao_instituicao_id',
        'accepted_terms',
        'accepted_terms_2'
    ];

    public function inscricao_instituicao(){
        return $this->hasOne(InscricaoInstituicoes::class);
    }

    public function inscricao_documentos(){
        // return $this->hasMany(InscricaoDocumentos::class);
    }
}
