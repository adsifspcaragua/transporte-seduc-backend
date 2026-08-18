<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\AnaliseInscricaoRequest;
use App\Http\Requests\Inscricao\Instituicao\StoreInscricaoInstituicaoRequest;
use App\Http\Requests\Inscricao\StoreInscricaoRequest;
use App\Http\Requests\Inscricao\UpdateInscricaoRequest;
use App\Models\Inscricao;
use App\Services\Inscricao\InscricaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        $messages = match ($step) {
            0, 1 => (new StoreInscricaoRequest)->messages(),
            2 => (new StoreInscricaoInstituicaoRequest)->messages(),
            default => [],
        };

        // O estudante que retoma a lista de espera revalida os próprios dados.
        // Sem ignorar a inscrição dele, as regras `unique` acusariam o CPF, o
        // telefone e o e-mail que já são dele e travariam a etapa. O token
        // comprova que a inscrição lhe pertence.
        $inscricao = $this->inscricaoEmEdicao($request);

        if ($inscricao) {
            $rules = $this->ignorarInscricaoNasRegrasUnicas($rules, $inscricao);
        }

        Validator::make($data, $rules, $messages)->validate();

        return response()->json([
            'message' => 'Dados válidos.',
        ]);
    }

    /**
     * Resolve a inscrição que está sendo reeditada, se o token comprovar posse.
     *
     * Mesma credencial usada pelo middleware `inscricao.token`: sem ela o ID
     * sozinho não permite afrouxar a validação de outra inscrição.
     */
    private function inscricaoEmEdicao(Request $request): ?Inscricao
    {
        $id = $request->input('inscricao_id');

        if (! $id) {
            return null;
        }

        $inscricao = Inscricao::find($id);

        if (! $inscricao || ! $inscricao->access_token) {
            return null;
        }

        $token = (string) ($request->header('X-Inscricao-Token') ?? $request->input('token', ''));

        if ($token === '' || ! hash_equals($inscricao->access_token, $token)) {
            return null;
        }

        return $inscricao;
    }

    /**
     * Reescreve as regras `unique:inscricoes` para ignorar a própria inscrição.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function ignorarInscricaoNasRegrasUnicas(array $rules, Inscricao $inscricao): array
    {
        foreach ($rules as $campo => $regra) {
            $partes = is_array($regra) ? $regra : explode('|', (string) $regra);
            $alterou = false;

            foreach ($partes as $indice => $parte) {
                if (! is_string($parte) || ! str_starts_with($parte, 'unique:inscricoes')) {
                    continue;
                }

                $coluna = explode(',', $parte)[1] ?? $campo;
                $partes[$indice] = Rule::unique('inscricoes', $coluna)->ignore($inscricao->id);
                $alterou = true;
            }

            if ($alterou) {
                $rules[$campo] = $partes;
            }
        }

        return $rules;
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
