<?php

namespace Database\Factories;

use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitacaoReecadastro>
 */
class SolicitacaoReecadastroFactory extends Factory
{
    protected $model = SolicitacaoReecadastro::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'estudante_id' => Estudante::factory(),
            'periodo_id' => PeriodoReecadastro::factory()->aberto(),
            'status' => 'Pendente',
        ];
    }

    /** Sessao publica ativa: token valido para os envios. */
    public function comToken(string $token = 'token-de-teste'): static
    {
        return $this->state([
            'access_token' => $token,
            'token_expira_em' => now()->addHours(2),
        ]);
    }

    /** Solicitacao ja enviada pelo estudante, aguardando homologacao. */
    public function emAnalise(): static
    {
        return $this->state([
            'status' => 'Em analise',
            'aceite_veracidade' => true,
            'aceite_ciencia' => true,
            'enviada_em' => now(),
        ]);
    }
}
