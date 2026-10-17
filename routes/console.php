<?php

use App\Models\DesafioDispositivo;
use App\Models\TentativaPresenca;
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

Schedule::command('viagens:gerar')->dailyAt('00:10')->timezone(config('presenca.fuso'));

// Desafios vencidos e, se houver prazo de retencao definido (D10), tentativas
// antigas de presenca.
Schedule::command('model:prune', [
    '--model' => [DesafioDispositivo::class, TentativaPresenca::class],
])->dailyAt('03:00');
