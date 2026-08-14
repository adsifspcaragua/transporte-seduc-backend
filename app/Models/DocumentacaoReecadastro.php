<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentacaoReecadastro extends Model
{
    use HasFactory;

    protected $table = 'documentacoes_reecadastro';

    /**
     * Documentos exigidos no recadastro: slug usado na API e no arquivo em
     * disco => rótulo exibido ao estudante e à responsável.
     *
     * @var array<string, string>
     */
    public const TIPOS = [
        'declaracao_matricula' => 'Declaração de Matrícula',
        'cronograma_aulas' => 'Cronograma de Aulas',
        'comprovante_residencia' => 'Comprovante de Residência',
    ];

    /**
     * Documentos para os quais o estudante pode declarar que ainda não possui
     * e pedir prazo adicional. O comprovante de residência é sempre exigido.
     *
     * @var list<string>
     */
    public const TIPOS_COM_PRAZO = [
        'declaracao_matricula',
        'cronograma_aulas',
    ];

    protected $fillable = [
        'estudante_id',
        'solicitacao_id',
        'type',
        'file_path',
        'nome_original',
        'status',
        'observacoes',
    ];

    public function estudante()
    {
        return $this->belongsTo(Estudante::class, 'estudante_id');
    }

    public function solicitacao()
    {
        return $this->belongsTo(SolicitacaoReecadastro::class, 'solicitacao_id');
    }

    /** Rótulo do tipo, com fallback legível para valores antigos. */
    public function getLabelAttribute(): string
    {
        return self::TIPOS[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::TIPOS);
    }
}
