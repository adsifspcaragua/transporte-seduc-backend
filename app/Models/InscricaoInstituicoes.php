<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscricaoInstituicoes extends Model
{
    protected $table = "inscricao_instituicaos";
    protected $fillable = [
            'course',
            'semester',
            'expected_completion', // DATA
            'shift', // MATUTINO / NOTURNO
            'city_destination',
            'used_transport',
            'days_of_week',
            'line_id',
            'has_scholarship',
            'scholarship_type',
            'instituicao_id',
            'inscricao_id'
    ];

    public function inscricao(){
        return $this->belongsTo(Inscricao::class);
    }


    
}
