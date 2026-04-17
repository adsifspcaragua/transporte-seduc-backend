<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instituicao extends Model
{
    protected $table = "instituicoes";
    protected $casts = [
        'linhas_ids' => 'array',
    ];
    protected $fillable = [ 
        'name',
        'linhas_ids'
    ];
}
