<?php

namespace Database\Seeders;

use App\Models\AtribuicaoVeiculo;
use App\Models\Estudante;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\LinhaPonto;
use App\Models\PontoEmbarque;
use App\Models\Role;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cenario de desenvolvimento da presenca por QR: uma van por linha, horarios
 * de ida e volta tirados das linhas, pontos de embarque e um estudante com
 * login para testar o fluxo no celular.
 *
 * Os pontos e raios sao FICTICIOS: so servem para o fluxo funcionar em
 * desenvolvimento. Os reais dependem do levantamento com o PO (D01 a D03).
 */
class PresencaSeeder extends Seeder
{
    /** Ponto de ida de cada linha (nome do seeder de linhas => [ponto, lat, lon]). */
    private const PONTOS_IDA = [
        'Linha Centro' => ['Praça Cândido Mota (fictício)', -23.6203, -45.4126],
        'Linha Sul' => ['Porto Novo (fictício)', -23.6731, -45.4402],
        'Linha Norte' => ['Massaguaçu (fictício)', -23.5893, -45.3468],
        'Linha Noturna' => ['Rodoviária de Caraguatatuba (fictício)', -23.6233, -45.4177],
    ];

    /** A volta embarca no destino, em Sao Jose dos Campos. */
    private const PONTO_VOLTA = ['Polo universitário de São José dos Campos (fictício)', -23.2105, -45.8770];

    public function run(): void
    {
        $volta = PontoEmbarque::updateOrCreate(
            ['nome' => self::PONTO_VOLTA[0]],
            ['latitude' => self::PONTO_VOLTA[1], 'longitude' => self::PONTO_VOLTA[2], 'raio_metros' => 300, 'ativo' => true],
        );

        $qrs = [];

        foreach (Linha::orderBy('id')->get()->values() as $i => $linha) {
            $van = Veiculo::firstOrCreate(['identificacao' => sprintf('Van %02d', $i + 1)], ['ativo' => true]);

            // Rodar de novo nao troca o QR ja impresso.
            if ($van->qr_token_hash === null) {
                $qrs[$van->identificacao] = Veiculo::urlDoQr($van->gerarTokenQr());
            }

            AtribuicaoVeiculo::firstOrCreate(
                ['veiculo_id' => $van->id, 'linha_id' => $linha->id, 'inicio' => '2026-01-01'],
                ['motivo' => 'Atribuição inicial (seeder)'],
            );

            $horarios = array_filter([
                HorarioViagem::IDA => $linha->departure_time,
                HorarioViagem::VOLTA => $linha->return_time,
            ]);

            foreach ($horarios as $sentido => $saida) {
                HorarioViagem::updateOrCreate(
                    ['linha_id' => $linha->id, 'sentido' => $sentido],
                    ['dias_semana' => [1, 2, 3, 4, 5], 'horario_saida' => $saida, 'ativo' => true],
                );
            }

            [$nome, $latitude, $longitude] = self::PONTOS_IDA[$linha->name] ?? ["Ponto {$linha->name} (fictício)", -23.6203, -45.4126];
            $ida = PontoEmbarque::updateOrCreate(
                ['nome' => $nome],
                ['latitude' => $latitude, 'longitude' => $longitude, 'raio_metros' => 300, 'ativo' => true],
            );

            LinhaPonto::firstOrCreate(['linha_id' => $linha->id, 'ponto_id' => $ida->id, 'sentido' => HorarioViagem::IDA], ['ordem' => 1]);
            LinhaPonto::firstOrCreate(['linha_id' => $linha->id, 'ponto_id' => $volta->id, 'sentido' => HorarioViagem::VOLTA], ['ordem' => 1]);
        }

        $this->contaDeEstudante();

        foreach ($qrs as $van => $url) {
            $this->command?->info("QR da {$van}: {$url}");
        }
    }

    /**
     * estudante@example.com, ligado a um estudante ativo da Linha Centro, para
     * testar a autorizacao do dispositivo e a presenca.
     */
    private function contaDeEstudante(): void
    {
        $estudante = Estudante::where('situacao', 'Ativo')
            ->whereHas('linha', fn ($q) => $q->where('name', 'Linha Centro'))
            ->orderBy('id')
            ->first();

        if (! $estudante) {
            return;
        }

        $user = User::updateOrCreate(
            ['email' => 'estudante@example.com'],
            [
                'name' => $estudante->name,
                'password' => Hash::make('12345678'),
                'cpf' => $estudante->cpf,
                'ativo' => true,
            ],
        );
        $user->roles()->sync(Role::where('title', 'estudante')->pluck('id'));

        Estudante::where('user_id', $user->id)->whereKeyNot($estudante->id)->update(['user_id' => null]);
        $estudante->update(['user_id' => $user->id]);
    }
}
