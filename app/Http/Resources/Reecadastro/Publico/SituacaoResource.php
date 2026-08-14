<?php

namespace App\Http\Resources\Reecadastro\Publico;

use App\Models\DocumentacaoReecadastro;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Situação do recadastro devolvida ao estudante que acessou pelo CPF.
 *
 * Traz apenas o necessário para a tela pública: o que já foi enviado, o que
 * ainda falta e se o envio está liberado.
 */
class SituacaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $periodoAberto = $this->periodo && $this->periodo->status === 'Aberto';

        return [
            'solicitacao_id' => $this->id,
            'token' => $this->access_token,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            'pode_enviar' => $periodoAberto && $this->aceitaEnvio(),
            'prazo_matricula' => $this->prazo_matricula,
            'prazo_cronograma' => $this->prazo_cronograma,
            'enviada_em' => $this->enviada_em,
            'periodo' => [
                'id' => $this->periodo?->id,
                'referencia' => $this->periodo?->referencia,
                'data_inicio' => $this->periodo?->data_inicio?->toDateString(),
                'data_fim' => $this->periodo?->data_fim?->toDateString(),
                'status' => $this->periodo?->status,
            ],
            'estudante' => [
                'id' => $this->estudante?->id,
                'name' => $this->estudante?->name,
                'email' => $this->estudante?->email,
                'phone' => $this->estudante?->phone,
                'address' => $this->estudante?->address,
            ],
            'documentos' => $this->documentosExigidos(),
        ];
    }

    /**
     * Lista os documentos do recadastro com a situação de cada um.
     *
     * @return array<int, array<string, mixed>>
     */
    private function documentosExigidos(): array
    {
        $enviados = $this->documentos;

        return collect(DocumentacaoReecadastro::TIPOS)
            ->map(function (string $label, string $tipo) use ($enviados) {
                $documento = $enviados->firstWhere('type', $tipo);

                return [
                    'type' => $tipo,
                    'label' => $label,
                    'aceita_prazo' => in_array($tipo, DocumentacaoReecadastro::TIPOS_COM_PRAZO, true),
                    'status' => $documento?->status,
                    'nome_original' => $documento?->nome_original,
                    'enviado_em' => $documento?->updated_at,
                    'pendente' => ! $documento || $documento->status === 'Rejeitado',
                    'observacoes' => $documento?->observacoes,
                ];
            })
            ->values()
            ->all();
    }
}
