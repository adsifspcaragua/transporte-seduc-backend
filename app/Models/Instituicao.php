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

    public function inscricoesInstituicoes()
    {
        return $this->hasMany(InscricaoInstituicoes::class, 'instituicao_id');
    }
    public function estudantesInstituicoes()
    {
        return $this->hasMany(Estudante::class, 'instituicao_id');
    }

}
