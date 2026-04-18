<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudante extends Model
{
    protected $table = "estudantes";
   protected $casts = [
    'days_of_week' => 'array',
    ];

    protected $fillable = [
        "name", 
        "email",
        "cpf",
        "birth_date", 
        "phone",
        "address", 
        "start_time", 
        "end_time", 
        "days_of_week", 
        "observation", 
        "status", 
        "linha_id", 
        "user_id",
        "inscricao_id",
        "instituicao_id",
    ];


    public function inscricao(){
        return $this->belongsTo(Inscricao::class, "inscricao_id");
    }

    public function instituicao(){
        return $this->belongsTo(Instituicao::class, "instituicao_id");
    }

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

     public function linha(){
        //return $this->belongsTo(Linha::class, "linha_id");
    }
}
