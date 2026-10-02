<?php

namespace App\Exports;

use App\Models\Estudante;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Fonte única das linhas exportadas: CSV, XLSX e PDF mostram os mesmos
 * dados, com os mesmos rótulos da tabela de estudantes do painel.
 */
class EstudantesExport implements FromArray, ShouldAutoSize, WithCustomCsvSettings, WithHeadings, WithStyles
{
    private const TURNOS = [1 => 'Matutino', 2 => 'Noturno'];

    /** @var array<int, array<string, string>> */
    private array $linhas;

    /**
     * @param  Collection<int, Estudante>  $estudantes  com instituicao, linha e inscricao.inscricao_instituicao
     */
    public function __construct(Collection $estudantes)
    {
        $this->linhas = $estudantes->map(fn (Estudante $estudante) => $this->linha($estudante))->all();
    }

    public function headings(): array
    {
        return [
            'Nome',
            'Email',
            'CPF',
            'Data de Nascimento',
            'Telefone',
            'Endereço',
            'Instituição',
            'Curso',
            'Semestre',
            'Turno',
            'Linha',
            'Status',
            'Situação',
        ];
    }

    public function array(): array
    {
        return array_map(fn (array $linha) => array_values($linha), $this->linhas);
    }

    /**
     * Ponto e vírgula e BOM: é o que o Excel em português espera para abrir
     * o arquivo em colunas e com acentuação correta.
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'use_bom' => true,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '084E80']],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function linhas(): array
    {
        return $this->linhas;
    }

    /**
     * @return array<string, int>
     */
    public function resumo(): array
    {
        $contar = fn (string $campo, string $valor) => count(array_filter(
            $this->linhas,
            fn (array $linha) => $linha[$campo] === $valor,
        ));

        return [
            'total' => count($this->linhas),
            'ativos' => $contar('situacao', 'Ativo'),
            'inativos' => $contar('situacao', 'Inativo'),
            'aprovados' => $contar('status', 'Aprovado'),
            'lista_de_espera' => $contar('status', 'Lista de espera'),
            'recusados' => $contar('status', 'Recusado'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function linha(Estudante $estudante): array
    {
        $inscricao = $estudante->inscricao;
        $academico = $inscricao?->inscricao_instituicao;

        return [
            'nome' => $estudante->name ?? $inscricao?->name ?? '',
            'email' => $estudante->email ?? $inscricao?->email ?? '',
            'cpf' => $this->cpf($estudante->cpf ?? $inscricao?->cpf),
            'nascimento' => $this->data($estudante->birth_date ?? $inscricao?->birth_date),
            'telefone' => $this->telefone($estudante->phone ?? $inscricao?->phone),
            'endereco' => $estudante->address ?? $inscricao?->address ?? '',
            'instituicao' => $estudante->instituicao?->name ?? '',
            'curso' => trim((string) $academico?->course),
            'semestre' => $this->semestre($academico?->semester),
            'turno' => self::TURNOS[(int) $academico?->shift] ?? '',
            'linha' => $estudante->linha?->name ?? 'Sem linha',
            'status' => (string) $estudante->status,
            'situacao' => (string) $estudante->situacao,
        ];
    }

    private function cpf(?string $cpf): string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);

        return strlen($digitos) === 11
            ? vsprintf('%s.%s.%s-%s', [substr($digitos, 0, 3), substr($digitos, 3, 3), substr($digitos, 6, 3), substr($digitos, 9)])
            : (string) $cpf;
    }

    private function telefone(?string $telefone): string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefone);

        return match (strlen($digitos)) {
            11 => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7)),
            10 => sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6)),
            default => (string) $telefone,
        };
    }

    private function data(mixed $data): string
    {
        return $data ? Carbon::parse($data)->format('d/m/Y') : '';
    }

    private function semestre(?string $semestre): string
    {
        $semestre = trim((string) $semestre);

        return ctype_digit($semestre) ? "{$semestre}º semestre" : $semestre;
    }
}
