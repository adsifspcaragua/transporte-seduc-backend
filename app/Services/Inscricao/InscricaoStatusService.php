<?php

namespace App\Services\Inscricao;

use App\Models\Inscricao;

class InscricaoStatusService
{
    public function isComplete(?Inscricao $inscricao): bool
    {
        if (! $inscricao) {
            return false;
        }

        $inscricaoCompleta = Inscricao::with(['inscricao_instituicao', 'inscricao_documentos'])
            ->find($inscricao->id);

        if (! $inscricaoCompleta) {
            return false;
        }

        $camposInscricao = [
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
            'number',
            'mother_name',
            'accepted_terms',
            'accepted_terms_2',
        ];

        foreach ($camposInscricao as $campo) {
            if ($inscricaoCompleta->{$campo} === null) {
                return false;
            }
        }

        $instituicao = $inscricaoCompleta->inscricao_instituicao;

        if (! $instituicao) {
            return false;
        }

        $camposInstituicao = [
            'course',
            'semester',
            'expected_completion',
            'instituicao_id',
            'shift',
            'city_destination',
            'used_transport',
            'has_scholarship',
        ];

        foreach ($camposInstituicao as $campo) {
            if ($instituicao->{$campo} === null) {
                return false;
            }
        }

        $documentosObrigatorios = [
            'foto',
            'identidade',
            'residencia',
            'historico',
            'matricula',
            'cronograma',
            'declaracao',
        ];
        
        $todosValidos = true;

        foreach($documentosObrigatorios as $obrigatorio){

            $doc = $inscricaoCompleta->inscricao_documentos->firstWhere('name', $obrigatorio);

            if(!$doc || $doc->status !== 'Em analise'){
                $todosValidos = false;
                break;
            }
        }

        return $todosValidos;
    }

    public function refreshStatus(Inscricao $inscricao): Inscricao
    {
        $inscricao->update([
            'status' => $this->isComplete($inscricao) ? 'Em analise' : 'Incompleto',
        ]);

        return $inscricao->refresh();
    }

    public function acceptInscricao(Inscricao $inscricao): Inscricao
    {
        $inscricao->update([
            'status' => "Aprovada"
        ]);

        return $inscricao->refresh();
    }

    public function refuseInscricao(Inscricao $inscricao): Inscricao
    {
        //TODO FAZER O REGISTRO DO MOTIVO
        $inscricao->update([
            'status' => "Rejeitada"
        ]);

        return $inscricao->refresh();
    }
}
