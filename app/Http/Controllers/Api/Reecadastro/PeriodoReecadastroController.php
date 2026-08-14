<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Periodo\StorePeriodoRequest;
use App\Http\Requests\Reecadastro\Periodo\UpdatePeriodoRequest;
use App\Services\Reecadastro\PeriodoReecadastroService;

/**
 * @group Recadastro
 *
 * Periodos de recadastro: as duas janelas anuais em que os estudantes precisam
 * reenviar a documentacao. Somente um periodo fica aberto por vez.
 *
 * @authenticated
 */
class PeriodoReecadastroController extends Controller
{
    public function __construct(private readonly PeriodoReecadastroService $periodoReecadastroService) {}

    /**
     * Listar periodos de recadastro.
     *
     * Retorna os periodos cadastrados, do mais recente para o mais antigo.
     */
    public function index()
    {
        return $this->periodoReecadastroService->index();
    }

    /**
     * Cadastrar periodo de recadastro.
     *
     * Cria o periodo ja fechado; use a rota de abrir para liberar aos estudantes.
     */
    public function store(StorePeriodoRequest $request)
    {
        return $this->periodoReecadastroService->store($request->validated());
    }

    /**
     * Exibir periodo de recadastro.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function show(string $id)
    {
        return $this->periodoReecadastroService->show($id);
    }

    /**
     * Atualizar periodo de recadastro.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function update(UpdatePeriodoRequest $request, string $id)
    {
        return $this->periodoReecadastroService->update($request->validated(), $id);
    }

    /**
     * Abrir periodo de recadastro.
     *
     * Libera o recadastro para os estudantes e fecha qualquer periodo anterior.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function abrir(string $id)
    {
        return $this->periodoReecadastroService->abrir($id);
    }

    /**
     * Fechar periodo de recadastro.
     *
     * Encerra os envios; as solicitacoes ja recebidas continuam disponiveis para analise.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function fechar(string $id)
    {
        return $this->periodoReecadastroService->fechar($id);
    }

    /**
     * Remover periodo de recadastro.
     *
     * So e possivel enquanto o periodo nao tiver solicitacoes.
     *
     * @urlParam periodo integer required ID do periodo. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->periodoReecadastroService->destroy($id);
    }
}
