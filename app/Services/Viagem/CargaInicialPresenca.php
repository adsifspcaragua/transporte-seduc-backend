<?php

namespace App\Services\Viagem;

use App\Models\AtribuicaoVeiculo;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\LinhaPonto;
use App\Models\Veiculo;
use App\Services\Auditoria\AuditoriaService;
use App\Support\HorarioLocal;

/**
 * Prepara um ambiente que ja tem linhas para receber a presenca por QR.
 *
 * Pode rodar de novo: so cria o que falta. Nunca inventa ponto de embarque,
 * porque coordenadas e raio dependem do levantamento com o PO (D01 a D03).
 */
class CargaInicialPresenca
{
    public function __construct(
        private readonly GeradorViagens $gerador,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * @return list<array{linha: string, horarios_criados: int, horarios: int, van: string|null, qr_url: string|null, pontos: int}>
     */
    public function executar(bool $criarVans = false, int $diasDeViagens = 7): array
    {
        $hoje = HorarioLocal::hoje();
        $situacao = [];

        foreach (Linha::orderBy('id')->get() as $linha) {
            $horariosCriados = $this->criarHorarios($linha);
            $qrUrl = null;

            if ($criarVans && AtribuicaoVeiculo::veiculoDaLinha($linha->id, $hoje) === null) {
                $qrUrl = $this->criarVan($linha);
            }

            $veiculoId = AtribuicaoVeiculo::veiculoDaLinha($linha->id, $hoje);

            $situacao[] = [
                'linha' => $linha->name,
                'horarios_criados' => $horariosCriados,
                'horarios' => HorarioViagem::where('linha_id', $linha->id)->where('ativo', true)->count(),
                'van' => $veiculoId ? Veiculo::whereKey($veiculoId)->value('identificacao') : null,
                'qr_url' => $qrUrl,
                'pontos' => LinhaPonto::where('linha_id', $linha->id)->count(),
            ];
        }

        $this->gerador->gerar($hoje, $hoje->copy()->addDays($diasDeViagens));

        return $situacao;
    }

    private function criarHorarios(Linha $linha): int
    {
        $criados = 0;

        foreach ([HorarioViagem::IDA => $linha->departure_time, HorarioViagem::VOLTA => $linha->return_time] as $sentido => $saida) {
            if ($saida === null || HorarioViagem::where('linha_id', $linha->id)->where('sentido', $sentido)->exists()) {
                continue;
            }

            HorarioViagem::create([
                'linha_id' => $linha->id,
                'sentido' => $sentido,
                'dias_semana' => config('presenca.dias_padrao'),
                'horario_saida' => $saida,
                'ativo' => true,
            ]);
            $criados++;
        }

        return $criados;
    }

    /**
     * O token so existe aqui: quem roda o comando precisa guardar a URL para
     * gerar a arte do QR. Depois, so rotacionando.
     */
    private function criarVan(Linha $linha): string
    {
        $van = Veiculo::create(['identificacao' => "Van da {$linha->name}", 'ativo' => true]);
        $token = $van->gerarTokenQr();

        AtribuicaoVeiculo::create([
            'veiculo_id' => $van->id,
            'linha_id' => $linha->id,
            'inicio' => HorarioLocal::hoje()->toDateString(),
            'motivo' => 'Carga inicial',
        ]);

        $this->auditoria->registrar(null, 'veiculo.criar', $van, ['identificacao' => $van->identificacao, 'origem' => 'carga inicial']);

        return Veiculo::urlDoQr($token);
    }
}
