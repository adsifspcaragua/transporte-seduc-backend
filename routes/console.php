<?php

use App\Models\CodigoAcesso;
use App\Models\DesafioDispositivo;
use App\Models\TentativaPresenca;
use App\Services\Viagem\CargaInicialPresenca;
use App\Services\Viagem\GeradorViagens;
use App\Support\HorarioLocal;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// As viagens tambem sao geradas sob demanda na leitura do QR; gerar antes so
// deixa a gestao ajustar (trocar van, cancelar) os proximos dias com folga.
Artisan::command('viagens:gerar {--dias=7 : Quantos dias a partir de hoje}', function (GeradorViagens $gerador) {
    $hoje = HorarioLocal::hoje();
    $ate = $hoje->copy()->addDays(max(0, (int) $this->option('dias')));
    $criadas = $gerador->gerar($hoje, $ate);
    $conflitos = $gerador->conflitos($hoje, $ate);

    $this->info("{$criadas} viagem(ns) criada(s) até {$ate->toDateString()}.");

    foreach ($conflitos as $conflito) {
        $this->warn("Conflito: van #{$conflito['veiculo_id']} em {$conflito['data']} nas viagens ".implode(' e ', $conflito['viagens']).'.');
    }
})->purpose('Gera as viagens datadas dos próximos dias a partir dos horários');

Artisan::command('presenca:carga-inicial {--vans : Cria uma van com QR para cada linha sem van}', function (CargaInicialPresenca $carga) {
    $situacao = $carga->executar((bool) $this->option('vans'));

    $this->table(
        ['Linha', 'Horários criados', 'Horários ativos', 'Van', 'Pontos'],
        array_map(fn (array $linha) => [
            $linha['linha'],
            $linha['horarios_criados'],
            $linha['horarios'],
            $linha['van'] ?? '-',
            $linha['pontos'],
        ], $situacao),
    );

    foreach ($situacao as $linha) {
        if ($linha['qr_url']) {
            $this->info("QR de {$linha['van']} (só aparece agora): {$linha['qr_url']}");
        }

        if (! $linha['van']) {
            $this->warn("{$linha['linha']}: sem van. Nenhum QR leva às viagens desta linha.");
        }

        if ($linha['pontos'] === 0) {
            $this->warn("{$linha['linha']}: sem ponto de embarque. As presenças vão para revisão manual.");
        }
    }
})->purpose('Prepara horários, viagens e (opcionalmente) vans para a presença por QR');

Schedule::command('viagens:gerar')->dailyAt('00:10')->timezone(config('presenca.fuso'));

// Desafios vencidos e, se houver prazo de retencao definido (D10), tentativas
// antigas de presenca.
Schedule::command('model:prune', [
    '--model' => [CodigoAcesso::class, DesafioDispositivo::class, TentativaPresenca::class],
])->dailyAt('03:00');
