<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    use HasFactory;

    protected $table = 'inscricoes';

    protected $fillable = [
        'name',
        'cpf',
        'rg',
        'birth_date',
        'phone',
        'email',
        'cep',
        'address',
        'neighborhood',
        'city',
        'complement',
        'number',
        'father_name',
        'mother_name',
        'observation',
        'status',
        'accepted_terms',
        'accepted_terms_2',
        'access_token',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'accepted_terms' => 'boolean',
        'accepted_terms_2' => 'boolean',
    ];

    public function inscricao_instituicao()
    {
        return $this->hasOne(InscricaoInstituicoes::class, 'inscricao_id');
    }

    public function estudante()
    {
        return $this->hasOne(Estudante::class);
    }
}
