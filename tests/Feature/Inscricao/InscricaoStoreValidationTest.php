<?php

namespace Tests\Feature\Inscricao;

use App\Models\Inscricao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta).
 *
 * Tecnicas aplicadas:
 *  - Particionamento em classes de equivalencia
 *  - Analise do valor-limite
 *
 * Alvo: regras do StoreInscricaoRequest via POST /api/inscricoes (rota publica).
 *
 * Foco nos campos com limites bem definidos:
 *   cpf    -> size:11  (exatamente 11 caracteres)
 *   name   -> min:3, max:255
 *   number -> integer, min:1
 *   status -> prohibited (campo controlado pelo sistema)
 */
class InscricaoStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Payload minimo valido. Campos unicos parametrizados para evitar colisao
     * com a regra unique entre testes/criacoes.
     *
     * @return array<string, mixed>
     */
    private function payloadValido(array $override = []): array
    {
        return array_merge([
            'name' => 'Joao da Silva',
            'cpf' => '12345678901',
        ], $override);
    }

    // ---------- CAMINHO VALIDO (classe valida) ----------

    public function test_inscricao_minima_valida_e_criada(): void
    {
        $this->postJson('/api/inscricoes', $this->payloadValido())
            ->assertCreated();

        $this->assertDatabaseHas('inscricoes', ['cpf' => '12345678901']);
    }

    // ---------- CPF :: ANALISE DO VALOR-LIMITE (size:11) ----------

    public function test_cpf_com_exatamente_11_caracteres_e_aceito(): void
    {
        // Valor exatamente no limite -> valido.
        $this->postJson('/api/inscricoes', $this->payloadValido(['cpf' => '99999999999']))
            ->assertCreated();
    }

    public function test_cpf_com_10_caracteres_e_rejeitado(): void
    {
        // Limite inferior - 1.
        $this->postJson('/api/inscricoes', $this->payloadValido(['cpf' => '1234567890']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');
    }

    public function test_cpf_com_12_caracteres_e_rejeitado(): void
    {
        // Limite superior + 1.
        $this->postJson('/api/inscricoes', $this->payloadValido(['cpf' => '123456789012']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');
    }

    public function test_cpf_ausente_e_rejeitado(): void
    {
        $this->postJson('/api/inscricoes', ['name' => 'Sem Cpf'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');
    }

    public function test_cpf_duplicado_e_rejeitado(): void
    {
        Inscricao::factory()->create(['cpf' => '55544433322']);

        $this->postJson('/api/inscricoes', $this->payloadValido(['cpf' => '55544433322']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');
    }

    // ---------- NAME :: ANALISE DO VALOR-LIMITE (min:3, max:255) ----------

    public function test_name_com_2_caracteres_e_rejeitado(): void
    {
        // Limite inferior - 1.
        $this->postJson('/api/inscricoes', $this->payloadValido(['name' => 'Jo']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_name_com_3_caracteres_e_aceito(): void
    {
        // Limite inferior -> valido.
        $this->postJson('/api/inscricoes', $this->payloadValido(['name' => 'Ana']))
            ->assertCreated();
    }

    public function test_name_com_256_caracteres_e_rejeitado(): void
    {
        // Limite superior + 1.
        $this->postJson('/api/inscricoes', $this->payloadValido(['name' => str_repeat('a', 256)]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    // ---------- NUMBER :: classe invalida (min:1) ----------

    public function test_number_zero_e_rejeitado(): void
    {
        // Limite inferior - 1 (min:1).
        $this->postJson('/api/inscricoes', $this->payloadValido(['number' => 0]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('number');
    }

    // ---------- STATUS :: campo proibido (prohibited) ----------

    public function test_status_enviado_pelo_cliente_e_rejeitado(): void
    {
        $this->postJson('/api/inscricoes', $this->payloadValido(['status' => 'Aprovada']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }
}
