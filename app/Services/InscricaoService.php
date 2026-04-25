<?php
namespace App\Services;

use App\Models\Inscricao;

class InscricaoService {

    public function isComplete(Inscricao $inscricao){
        $inscricaoCompleta = Inscricao::with(['inscricao_instituicao','inscricao_documentos'])
        ->find($inscricao->id)->toArray();

        $inscricoesObrigatorios = [
            'name','cpf','rg','birth_date','phone','email',
            'cep','address','neighborhood','city','number',
            'mother_name','accepted_terms','accepted_terms_2',
        ];

        $instituicoesObrigatorios = [
            'course','semester','expected_completion','instituicao_id',
            'shift','city_destination','used_transport','has_scholarship',
        ];

        $documentosObrigatorios = [
            'foto','identidade','residencia','historico',
            'matricula','cronograma','declaracao',
        ];

        foreach ($inscricoesObrigatorios as $campo) {
            if (!array_key_exists($campo, $inscricaoCompleta) || $inscricaoCompleta[$campo] === null) {
                return false;
            }
        }
        $instituicao = $inscricaoCompleta['inscricao_instituicao'] ?? null;

        if (!$instituicao) {
            return false;
        }

        foreach ($instituicoesObrigatorios as $campo) {
            if (!array_key_exists($campo, $instituicao) || $instituicao[$campo] === null) {
                return false;
            }
        }

        $nomesDaInscricao = $inscricao->inscricao_documentos
        ->pluck('name')
        ->toArray();

        $faltando = array_diff($documentosObrigatorios, $nomesDaInscricao);

        if (!empty($faltando)) {
            return false;
        }

        
        return true;
    }

}