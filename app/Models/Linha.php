<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linha extends Model
{
    protected $table = "linhas";

    protected $fillable = [ 
        'name',
        'description',
        'departure_time',
        'return_time',
        'max_capacity',
    ];

    public function estudantes(){
       return $this->hasMany(Estudante::class, "linha_id");
    }

}
