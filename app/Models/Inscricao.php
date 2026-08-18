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
        'campos_pendentes',
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
        'campos_pendentes' => 'array',
    ];

    /**
     * Campos que a responsavel pode devolver para correcao.
     *
     * Espelha o que o estudante consegue editar na lista de espera, dados
     * pessoais e institucionais: apontar um campo que ele nao pode mexer so
     * geraria uma pendencia sem saida. O CPF fica de fora porque identifica a
     * inscricao e e unico; trocar de CPF e comecar outra inscricao.
     */
    public const CAMPOS_CORRIGIVEIS = [
        'name' => 'Nome completo',
        'rg' => 'RG',
        'father_name' => 'Nome do pai',
        'mother_name' => 'Nome da mãe',
        'birth_date' => 'Data de nascimento',
        'phone' => 'Telefone',
        'email' => 'E-mail',
        'cep' => 'CEP',
        'address' => 'Endereço',
        'number' => 'Número',
        'complement' => 'Complemento',
        'neighborhood' => 'Bairro',
        'city' => 'Cidade',
        'instituicao_id' => 'Instituição',
        'course' => 'Curso',
        'semester' => 'Semestre',
        'expected_completion' => 'Previsão de conclusão',
        'shift' => 'Turno',
        'city_destination' => 'Cidade de destino',
        'days_of_week' => 'Dias de uso do transporte',
        'has_scholarship' => 'Bolsa de estudos',
    ];

    /** @return list<string> */
    public static function camposCorrigiveis(): array
    {
        return array_keys(self::CAMPOS_CORRIGIVEIS);
    }

    public function inscricao_instituicao()
    {
        return $this->hasOne(InscricaoInstituicoes::class, 'inscricao_id');
    }

    public function estudante()
    {
        return $this->hasOne(Estudante::class);
    }

    public function inscricao_documentos()
    {
        return $this->hasMany(InscricaoDocumento::class, 'inscricao_id');
    }
}
