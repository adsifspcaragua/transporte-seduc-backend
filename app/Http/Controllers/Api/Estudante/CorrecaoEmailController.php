<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudante\CorrigirEmailRequest;
use App\Models\Estudante;
use App\Models\Inscricao;
use App\Services\Estudante\CorrecaoEmailService;

/**
 * @group Atendimento
 *
 * Correcao do e-mail de quem perdeu o acesso ao endereco cadastrado e, por isso,
 * nao recebe o codigo de acesso nem o link de senha.
 *
 * @authenticated
 */
class CorrecaoEmailController extends Controller
{
    public function __construct(private readonly CorrecaoEmailService $service) {}

    /**
     * Corrigir e-mail do estudante.
     *
     * Atualiza o cadastro, a inscricao e a conta do estudante, e invalida os
     * codigos e links enviados ao endereco antigo.
     *
     * @urlParam estudante integer required ID do estudante. Example: 1
     */
    public function estudante(CorrigirEmailRequest $request, Estudante $estudante)
    {
        return $this->corrigir($request);
    }

    /**
     * Corrigir e-mail de inscricao.
     *
     * Para quem ainda esta na lista de espera, em qualquer situacao da inscricao.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function inscricao(CorrigirEmailRequest $request, Inscricao $inscricao)
    {
        return $this->corrigir($request);
    }

    private function corrigir(CorrigirEmailRequest $request)
    {
        $data = $request->validated();

        return $this->service->corrigir($request->user(), $request->estudante(), $request->inscricao(), $data['email'], $data['justificativa']);
    }
}
