<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoDocumento extends Model
{
    use HasFactory;

    public const TIPOS = [
        'foto' => 'Foto',
        'identidade' => 'Documento de identidade',
        'residencia' => 'Comprovante de residência',
        'historico' => 'Histórico escolar',
        'matricula' => 'Declaração de matrícula',
        'declaracao' => 'Declaração complementar',
        'cronograma' => 'Cronograma de aulas',
    ];

    public const OBRIGATORIOS = [
        'foto',
        'identidade',
        'residencia',
        'historico',
        'matricula',
        'declaracao',
    ];

    protected $fillable = [
        'inscricao_id',
        'name',
        'type',
        'file_path',
        'nome_original',
        'status',
    ];

    public function inscricao()
    {
        return $this->belongsTo(Inscricao::class, 'inscricao_id');
    }
}
