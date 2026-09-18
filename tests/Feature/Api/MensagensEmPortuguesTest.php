<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL do idioma das respostas da API.
 *
 * O sistema atende a secretaria e os estudantes de Caraguatatuba, mas as
 * mensagens geradas pelo proprio Laravel chegavam em ingles ("Unauthenticated.",
 * "The name field must be at least 3 characters.", "Too Many Attempts.") e
 * algumas expunham detalhes internos. Estes casos prendem o comportamento.
 */
class MensagensEmPortuguesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'admin')->pluck('id'));

        return $user;
    }

    public function test_mensagem_de_validacao_vem_em_portugues_com_o_nome_do_campo(): void
    {
        Sanctum::actingAs($this->admin());

        $resposta = $this->postJson('/api/linha', ['name' => 'ab', 'max_capacity' => 0])
            ->assertStatus(422);

        // O nome do campo e o que a pessoa ve na tela, nao o nome da coluna.
        $this->assertSame('O campo nome deve ter ao menos 3 caracteres.', $resposta->json('errors.name.0'));
        $this->assertSame('O campo capacidade máxima deve ser ao menos 1.', $resposta->json('errors.max_capacity.0'));
    }

    public function test_resumo_do_erro_de_validacao_tambem_e_traduzido(): void
    {
        Sanctum::actingAs($this->admin());

        // O "(and 1 more error)" do Laravel vinha em ingles no campo `message`.
        $this->postJson('/api/linha', ['name' => 'ab', 'max_capacity' => 0])
            ->assertStatus(422)
            ->assertJsonPath('message', 'O campo nome deve ter ao menos 3 caracteres. (e mais 1 erro)');
    }

    public function test_campo_obrigatorio_sem_mensagem_propria_vem_em_portugues(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/linha', [])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'O campo nome é obrigatório.');
    }

    public function test_sem_autenticacao(): void
    {
        $this->getJson('/api/me')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Não autenticado. Faça login para continuar.');
    }

    public function test_rota_inexistente(): void
    {
        $this->getJson('/api/rota-que-nao-existe')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Rota não encontrada.');
    }

    public function test_metodo_nao_permitido(): void
    {
        Sanctum::actingAs($this->admin());

        $this->deleteJson('/api/dashboard')
            ->assertStatus(405)
            ->assertJsonPath('message', 'Método não permitido nesta rota.');
    }

    public function test_registro_inexistente_no_route_binding(): void
    {
        Sanctum::actingAs($this->admin());

        // Antes vazava o nome da classe: "No query results for model [App\Models\User] 999999".
        $this->putJson('/api/users/999999', ['name' => 'Fulano'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Registro não encontrado.');
    }

    public function test_excesso_de_tentativas(): void
    {
        $credenciais = ['email' => 'ninguem@example.com', 'password' => 'errada'];

        // O login aceita 5 tentativas por minuto.
        for ($tentativa = 0; $tentativa < 5; $tentativa++) {
            $this->postJson('/api/login', $credenciais);
        }

        $this->postJson('/api/login', $credenciais)
            ->assertStatus(429)
            ->assertJsonPath('message', fn (string $mensagem) => str_starts_with($mensagem, 'Muitas tentativas.'));
    }

    public function test_listagem_de_linhas_nao_anuncia_criacao_de_instituicao(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/linha', ['name' => 'Linha Teste', 'max_capacity' => 10])->assertOk();

        $this->getJson('/api/linha')
            ->assertOk()
            ->assertJsonPath('message', 'Linhas encontradas com sucesso');
    }
}
