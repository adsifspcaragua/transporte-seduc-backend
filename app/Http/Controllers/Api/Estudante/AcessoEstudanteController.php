<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudante\VerificarCodigoRequest;
use App\Http\Requests\Reecadastro\Publico\ConsultaCpfRequest;
use App\Services\Estudante\AcessoEstudanteService;

/**
 * @group Area do estudante
 *
 * Entrada sem login: o CPF dispara um codigo para o e-mail do cadastro, e so
 * com o codigo saem os dados e o token de edicao.
 */
class AcessoEstudanteController extends Controller
{
    public function __construct(private readonly AcessoEstudanteService $service) {}

    /**
     * Informar o CPF.
     *
     * CPF sem cadastro segue para a inscricao (`fluxo: inscricao`). Com cadastro,
     * envia o codigo ao e-mail (`fluxo: verificacao`), sem devolver nenhum dado.
     */
    public function solicitar(ConsultaCpfRequest $request)
    {
        return $this->service->solicitar($request->validated()['cpf']);
    }

    /**
     * Confirmar o codigo.
     *
     * Com o codigo correto, devolve o fluxo do estudante: lista de espera ou
     * recadastro, com os dados e o token.
     */
    public function verificar(VerificarCodigoRequest $request)
    {
        $data = $request->validated();

        return $this->service->verificar($data['cpf'], $data['codigo']);
    }
}
