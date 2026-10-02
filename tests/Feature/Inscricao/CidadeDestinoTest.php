<?php

namespace Tests\Feature\Inscricao;

use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Models\Instituicao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** O transporte universitário só leva estudantes para São José dos Campos. */
class CidadeDestinoTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: int, 1: string} */
    private function criarInscricao(): array
    {
        $dados = $this->postJson('/api/inscricoes', [
            'name' => 'Joao da Silva',
            'cpf' => '12345678901',
            'email' => 'joao@example.com',
        ])->assertCreated()->json();

        return [$dados['id'], $dados['token']];
    }

    public function test_aceita_sao_jose_dos_campos(): void
    {
        [$id, $token] = $this->criarInscricao();

        $this->postJson("/api/inscricoes/{$id}/instituicoes", [
            'token' => $token,
            'instituicao_id' => Instituicao::factory()->create()->id,
            'city_destination' => 'São José dos Campos',
        ])->assertSuccessful();

        $this->assertDatabaseHas('inscricao_instituicoes', [
            'inscricao_id' => $id,
            'city_destination' => InscricaoInstituicoes::CIDADE_DESTINO,
        ]);
    }

    public function test_recusa_outra_cidade_de_destino(): void
    {
        [$id, $token] = $this->criarInscricao();

        $this->postJson("/api/inscricoes/{$id}/instituicoes", [
            'token' => $token,
            'instituicao_id' => Instituicao::factory()->create()->id,
            'city_destination' => 'Caraguatatuba',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'city_destination' => 'A cidade de destino deve ser São José dos Campos.',
            ]);
    }

    public function test_atualizacao_tambem_recusa_outra_cidade(): void
    {
        $inscricao = Inscricao::factory()->create(['access_token' => 'token-valido']);
        $dados = InscricaoInstituicoes::factory()->create(['inscricao_id' => $inscricao->id]);

        $this->patchJson("/api/inscricoes/{$inscricao->id}/instituicoes/{$dados->id}", [
            'token' => 'token-valido',
            'city_destination' => 'Ubatuba',
        ])->assertUnprocessable()->assertJsonValidationErrors('city_destination');

        $this->assertSame(InscricaoInstituicoes::CIDADE_DESTINO, $dados->refresh()->city_destination);
    }
}
