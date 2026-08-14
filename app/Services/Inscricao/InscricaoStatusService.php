<?php

namespace App\Services\Inscricao;

use App\Models\Inscricao;

class InscricaoStatusService
{
    /**
     * Uma inscrição está completa quando os dados pessoais e os dados
     * institucionais foram preenchidos e os dois termos foram aceitos.
     *
     * A lista de espera não exige documentos: eles só são pedidos no
     * recadastro, depois que o estudante já está no sistema.
     */
    public function isComplete(?Inscricao $inscricao): bool
    {
        if (! $inscricao) {
            return false;
        }

        $inscricaoCompleta = Inscricao::with('inscricao_instituicao')->find($inscricao->id);

        if (! $inscricaoCompleta) {
            return false;
        }

        $camposInscricao = [
            'name',
            'cpf',
            'birth_date',
            'phone',
            'email',
            'cep',
            'address',
            'neighborhood',
            'city',
            'number',
            'mother_name',
        ];

        foreach ($camposInscricao as $campo) {
            if ($inscricaoCompleta->{$campo} === null) {
                return false;
            }
        }

        // Os dois termos precisam estar efetivamente aceitos, não apenas preenchidos.
        if (! $inscricaoCompleta->accepted_terms || ! $inscricaoCompleta->accepted_terms_2) {
            return false;
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

        return true;
    }

    public function refreshStatus(Inscricao $inscricao): Inscricao
    {
        $inscricao->update([
            'status' => $this->isComplete($inscricao) ? 'Em analise' : 'Incompleto',
        ]);

        return $inscricao->refresh();
    }
}
