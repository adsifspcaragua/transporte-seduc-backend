<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EstudantesExport implements FromArray, WithHeadings
{
    public function __construct(
        private array $estudantes
    ) {}

    public function headings(): array
    {
        return [
            'Nome',
            'Email',
            'CPF',
            'Data de Nascimento',
            'Celular',
            'Endereço',
            'Instituição',
            'Status',
            'Linha',
            'Frequência',
        ];
    }

    public function array(): array
    {
        return array_map(function ($estudante) {
            return [
                $estudante['name'],
                $estudante['email'],
                $estudante['cpf'],
                $estudante['birth_date'],
                $estudante['phone'],
                $estudante['address'],
                $estudante['instituicao']['name'],
                $estudante['status'],
                $estudante['linha']['name'] ?? '-',
                'Em desenvolvimento'
            ];
        }, $this->estudantes);
    }
}