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
        'name',
        'email',
        'rg',
        'cpf',
        'birth_date',
        'phone',
        'address',
        'father_name',
        'mother_name',
        'course',
        'semester',
        'year_completion',
        'instituicao_id',
        'start_time',
        'end_time',
        'days_of_week',
        'has_scholarship',
        'scholarship_type',
        'observation',
        'status',
        'line_id',
        'port',
        'user_id'
    ];


    public function documentos()
{
    return $this->hasMany(Documento::class, 'estudante_id');
}

    public function inscricao(){
        return $this->belongsTo(Inscricao::class);
    }
}
