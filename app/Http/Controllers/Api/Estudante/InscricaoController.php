<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\AnaliseInscricaoRequest;
use App\Http\Requests\Inscricao\Instituicao\StoreInscricaoInstituicaoRequest;
use App\Http\Requests\Inscricao\StoreInscricaoRequest;
use App\Http\Requests\Inscricao\UpdateInscricaoRequest;
use App\Services\Inscricao\InscricaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @group Inscricoes
 *
 * Rotas para criar, acompanhar e atualizar inscricoes de estudantes.
 *
 * @authenticated
 */
class InscricaoController extends Controller
{
    public function __construct(private readonly InscricaoService $inscricaoService) {}

    /**
     * Listar inscricoes.
     *
     * Retorna todas as inscricoes cadastradas.
     */
    public function index()
    {
        return $this->inscricaoService->index();
    }

    /**
     * Criar inscricao.
     *
     * Cria uma nova inscricao e recalcula o status conforme os dados enviados.
     */
    public function store(StoreInscricaoRequest $request)
    {
        return $this->inscricaoService->store($request->validated());
    }

    /**
     * Validar etapa da inscricao.
     *
     * Valida os dados enviados sem criar ou atualizar uma inscricao.
     */
    public function validateStep(Request $request)
    {
        $step = (int) $request->input('step');
        $data = $request->input('data', []);

        if (! is_array($data)) {
            $data = [];
        }

        $rules = match ($step) {
            0, 1 => (new StoreInscricaoRequest)->rules(),
            2 => (new StoreInscricaoInstituicaoRequest)->rules(),
            default => [],
        };

        if (array_key_exists('inscricao_id', $rules)) {
            unset($rules['inscricao_id']);
        }

        Validator::make($data, $rules)->validate();

        return response()->json([
            'message' => 'Dados válidos.',
        ]);
    }

    /**
     * Exibir inscricao.
     *
     * Retorna os dados de uma inscricao especifica.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function show(string $id)
    {
        return $this->inscricaoService->show($id);
    }

    /**
     * Atualizar inscricao.
     *
     * Atualiza uma inscricao existente quando ela ainda nao esta em analise.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function update(UpdateInscricaoRequest $request, $id)
    {
        return $this->inscricaoService->update($request->validated(), $id);
    }

    /**
     * Remover inscricao.
     *
     * Remove uma inscricao cadastrada.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->inscricaoService->destroy($id);
    }

    /**
     * Analisar inscricao.
     *
     * Aprova ou recusa a inscricao do estudante. A aprovacao gera o registro de
     * estudante, apto ao beneficio; a recusa exige o motivo.
     *
     * @urlParam id integer required ID da inscricao. Example: 1
     */
    public function analise(AnaliseInscricaoRequest $request, string $id)
    {
        return $this->inscricaoService->analiseInscricao($id, $request->validated());
    }
}
