<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudante extends Model
{
    use HasFactory;

    protected $table = 'estudantes';

    protected $casts = [
        'days_of_week' => 'array',
    ];

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'birth_date',
        'phone',
        'address',
        'days_of_week',
        'observation',
        'status',
        'situacao',
        'linha_id',
        'user_id',
        'inscricao_id',
        'instituicao_id',
    ];

    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }

    public function instituicao()
    {
        return $this->belongsTo(Instituicao::class, 'instituicao_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linha()
    {
        return $this->belongsTo(Linha::class, 'linha_id');
    }

    public function solicitacoes_reecadastro()
    {
        return $this->hasMany(SolicitacaoReecadastro::class, 'estudante_id');
    }

    public function frequencias()
    {
        return $this->hasMany(Frequencia::class, 'estudante_id');
    }

    public function avisos_frequencia()
    {
        return $this->hasMany(AvisoFrequencia::class, 'estudante_id');
    }

    public function dispositivos()
    {
        return $this->hasMany(DispositivoEstudante::class, 'estudante_id');
    }

    /** A instalacao de navegador autorizada, se houver. Ha no maximo uma. */
    public function dispositivoAtivo()
    {
        return $this->hasOne(DispositivoEstudante::class, 'estudante_id')
            ->where('status', DispositivoEstudante::ATIVO);
    }

    public function solicitacoesDispositivo()
    {
        return $this->hasMany(SolicitacaoDispositivo::class, 'estudante_id');
    }

    public function autorizacoesLinha()
    {
        return $this->hasMany(AutorizacaoLinha::class, 'estudante_id');
    }

    public function presencas()
    {
        return $this->hasMany(Presenca::class, 'estudante_id');
    }
}
