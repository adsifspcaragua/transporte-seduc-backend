<?php

namespace Tests\Support;

use App\Models\AtribuicaoVeiculo;
use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\LinhaPonto;
use App\Models\PontoEmbarque;
use App\Models\Role;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\Dispositivo\AssinaturaEcdsa;
use Illuminate\Support\Carbon;

/**
 * Montagem do cenario da presenca por QR: linha com horario, van com QR,
 * ponto de embarque e estudante com conta e navegador autorizado.
 */
trait CenarioPresenca
{
    /**
     * Data e hora de Caraguatatuba, devolvida no fuso da aplicacao — como o
     * now() de producao. Viajar no tempo com um instante em outro fuso faz o
     * Carbon ler os datetimes do banco nesse fuso e mascara erros de conversao.
     */
    protected function local(string $dataHora): Carbon
    {
        return Carbon::parse($dataHora, config('presenca.fuso'))->setTimezone(config('app.timezone'));
    }

    protected function usuario(string $perfil): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $perfil)->pluck('id'));

        return $user;
    }

    /**
     * Estudante com conta de perfil "estudante" vinculada (estudantes.user_id).
     */
    protected function estudanteComConta(Linha $linha, string $situacao = 'Ativo'): Estudante
    {
        $user = $this->usuario('estudante');

        return Estudante::factory()->create([
            'linha_id' => $linha->id,
            'situacao' => $situacao,
            'user_id' => $user->id,
        ]);
    }

    protected function autorizarNavegador(Estudante $estudante, NavegadorDeTeste $navegador): DispositivoEstudante
    {
        $der = (new AssinaturaEcdsa)->chavePublica($navegador->chavePublica());

        return DispositivoEstudante::create([
            'estudante_id' => $estudante->id,
            'chave_publica' => base64_encode($der),
            'chave_publica_sha256' => hash('sha256', $der),
            'status' => DispositivoEstudante::ATIVO,
            'aprovado_em' => now(),
        ]);
    }

    /**
     * @return array{0: Veiculo, 1: string} A van e o token do QR em claro.
     */
    protected function vanDaLinha(Linha $linha, string $desde = '2026-01-01', ?string $ate = null): array
    {
        $van = Veiculo::factory()->create();
        $token = $van->gerarTokenQr();

        AtribuicaoVeiculo::create([
            'veiculo_id' => $van->id,
            'linha_id' => $linha->id,
            'inicio' => $desde,
            'fim' => $ate,
        ]);

        return [$van, $token];
    }

    protected function horario(Linha $linha, string $sentido = HorarioViagem::IDA, string $saida = '17:30:00', array $dias = [1, 2, 3, 4, 5]): HorarioViagem
    {
        return HorarioViagem::factory()->create([
            'linha_id' => $linha->id,
            'sentido' => $sentido,
            'horario_saida' => $saida,
            'dias_semana' => $dias,
            'janela_antes_min' => 15,
            'janela_depois_min' => 15,
        ]);
    }

    protected function ponto(Linha $linha, string $sentido = HorarioViagem::IDA, float $latitude = -23.62, float $longitude = -45.41, int $raio = 200, array $extra = []): LinhaPonto
    {
        $ponto = PontoEmbarque::factory()->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'raio_metros' => $raio,
        ]);

        return LinhaPonto::create([
            'linha_id' => $linha->id,
            'ponto_id' => $ponto->id,
            'sentido' => $sentido,
            ...$extra,
        ]);
    }
}
