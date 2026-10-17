<?php

namespace App\Http\Controllers\Api\Viagem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispositivo\MotivoRequest;
use App\Http\Requests\Viagem\AtribuicaoVeiculoRequest;
use App\Http\Requests\Viagem\VeiculoRequest;
use App\Services\Viagem\VeiculoService;
use Illuminate\Http\Request;

/**
 * @group Presenca - Vans e QR Code
 *
 * As vans, o QR fixo de cada uma e qual van atende cada linha. O token do QR
 * aparece so na resposta de quem o gera (cadastro ou rotacao).
 *
 * @authenticated
 */
class VeiculoController extends Controller
{
    public function __construct(private readonly VeiculoService $service) {}

    /**
     * Listar vans.
     *
     * Com a linha atendida hoje.
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * Exibir van.
     *
     * @urlParam veiculo integer required ID da van. Example: 1
     */
    public function show(string $veiculo)
    {
        return $this->service->show($veiculo);
    }

    /**
     * Cadastrar van.
     *
     * Devolve `qr.token` e `qr.url` uma unica vez, para gerar a arte do QR.
     */
    public function store(VeiculoRequest $request)
    {
        return $this->service->store($request->user(), $request->validated());
    }

    /**
     * Atualizar van.
     *
     * @urlParam veiculo integer required ID da van. Example: 1
     */
    public function update(VeiculoRequest $request, string $veiculo)
    {
        return $this->service->update($request->user(), $veiculo, $request->validated());
    }

    /**
     * Excluir van.
     *
     * So sem viagens nem presencas; caso contrario, inative.
     *
     * @urlParam veiculo integer required ID da van. Example: 1
     */
    public function destroy(Request $request, string $veiculo)
    {
        return $this->service->destroy($request->user(), $veiculo);
    }

    /**
     * Rotacionar QR.
     *
     * Gera um novo token; o anterior deixa de valer na hora. Exige reimprimir.
     *
     * @urlParam veiculo integer required ID da van. Example: 1
     */
    public function rotacionarQr(MotivoRequest $request, string $veiculo)
    {
        return $this->service->rotacionarQr($request->user(), $veiculo, $request->validated()['motivo']);
    }

    /**
     * Listar atribuicoes van x linha.
     */
    public function atribuicoes(Request $request)
    {
        $filtros = $request->validate([
            'veiculo_id' => 'sometimes|integer',
            'linha_id' => 'sometimes|integer',
            'data' => 'sometimes|date_format:Y-m-d',
        ]);

        return $this->service->atribuicoes($filtros);
    }

    /**
     * Atribuir van a linha.
     *
     * Permanente (fim nulo) ou troca excepcional (inicio = fim). Vale a de
     * inicio mais recente. Devolve conflitos de horario da van, se houver.
     */
    public function atribuir(AtribuicaoVeiculoRequest $request)
    {
        return $this->service->atribuir($request->user(), $request->validated());
    }

    /**
     * Encerrar ou corrigir atribuicao.
     *
     * @urlParam atribuicao integer required ID da atribuicao. Example: 1
     */
    public function atualizarAtribuicao(AtribuicaoVeiculoRequest $request, string $atribuicao)
    {
        return $this->service->atualizarAtribuicao($request->user(), $atribuicao, $request->validated());
    }

    /**
     * Remover atribuicao.
     *
     * @urlParam atribuicao integer required ID da atribuicao. Example: 1
     */
    public function removerAtribuicao(Request $request, string $atribuicao)
    {
        return $this->service->removerAtribuicao($request->user(), $atribuicao);
    }
}
