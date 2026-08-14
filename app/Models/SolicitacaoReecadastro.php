<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoReecadastro extends Model
{
    use HasFactory;

    protected $table = 'solicitacoes_reecadastro';

    protected $fillable = [
        'estudante_id',
        'periodo_id',
        'status',
        'observacoes',
        'prazo_matricula',
        'prazo_cronograma',
        'aceite_veracidade',
        'aceite_ciencia',
        'access_token',
        'token_expira_em',
        'enviada_em',
        'analisado_por',
        'analisado_em',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'prazo_matricula' => 'boolean',
        'prazo_cronograma' => 'boolean',
        'aceite_veracidade' => 'boolean',
        'aceite_ciencia' => 'boolean',
        'token_expira_em' => 'datetime',
        'enviada_em' => 'datetime',
        'analisado_em' => 'datetime',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function periodo()
    {
        return $this->belongsTo(PeriodoReecadastro::class, 'periodo_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentacaoReecadastro::class, 'solicitacao_id');
    }

    public function analisadoPor()
    {
        return $this->belongsTo(User::class, 'analisado_por');
    }

    /** O estudante ainda pode enviar ou reenviar arquivos. */
    public function aceitaEnvio(): bool
    {
        return in_array($this->status, ['Pendente', 'Pendencia'], true);
    }

    /** A homologação já decidiu sobre esta solicitação. */
    public function finalizada(): bool
    {
        return in_array($this->status, ['Aprovado', 'Rejeitado'], true);
    }
}
