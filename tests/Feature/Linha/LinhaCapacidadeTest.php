<?php

namespace Tests\Feature\Linha;

use App\Models\Estudante;
use App\Models\Linha;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da capacidade e do vinculo da linha.
 *
 * A capacidade existia como numero no cadastro, mas nada a verificava: dava para
 * colocar mais estudantes do que o onibus comporta. E linha_id aceitava qualquer
 * inteiro, inclusive de linha inexistente.
 */
class LinhaCapacidadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'gestor')->pluck('id'));
        Sanctum::actingAs($user);
    }

    private function linhaCom(int $capacidade, int $ativos = 0): Linha
    {
        $linha = Linha::factory()->create(['max_capacity' => $capacidade]);

        Estudante::factory()->count($ativos)->create([
            'linha_id' => $linha->id,
            'status' => 'Ativo',
        ]);

        return $linha;
    }

    public function test_nao_aloca_estudante_em_linha_lotada(): void
    {
        $linha = $this->linhaCom(capacidade: 1, ativos: 1);
        $estudante = Estudante::factory()->create(['status' => 'Ativo']);

        $this->putJson("/api/estudantes/{$estudante->id}", ['linha_id' => $linha->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('linha_id');

        $this->assertNull($estudante->refresh()->linha_id);
    }

    public function test_estudante_que_ja_esta_na_linha_nao_conta_contra_si_mesmo(): void
    {
        $linha = $this->linhaCom(capacidade: 1);
        $estudante = Estudante::factory()->create([
            'linha_id' => $linha->id,
            'status' => 'Ativo',
        ]);

        // Salvar o cadastro de quem ja ocupa a unica vaga nao pode ser recusado.
        $this->putJson("/api/estudantes/{$estudante->id}", ['linha_id' => $linha->id])
            ->assertOk();

        $this->assertSame($linha->id, $estudante->refresh()->linha_id);
    }

    public function test_estudante_inativo_nao_ocupa_vaga(): void
    {
        $linha = Linha::factory()->create(['max_capacity' => 1]);
        Estudante::factory()->create(['linha_id' => $linha->id, 'status' => 'Inativo']);

        $ativo = Estudante::factory()->create(['status' => 'Ativo']);

        $this->putJson("/api/estudantes/{$ativo->id}", ['linha_id' => $linha->id])
            ->assertOk();
    }

    public function test_linha_inexistente_e_recusada(): void
    {
        $estudante = Estudante::factory()->create(['status' => 'Ativo']);

        $this->putJson("/api/estudantes/{$estudante->id}", ['linha_id' => 999999])
            ->assertStatus(422)
            ->assertJsonValidationErrors('linha_id');
    }

    public function test_linha_pode_ser_desvinculada(): void
    {
        $linha = $this->linhaCom(capacidade: 10);
        $estudante = Estudante::factory()->create([
            'linha_id' => $linha->id,
            'status' => 'Ativo',
        ]);

        $this->putJson("/api/estudantes/{$estudante->id}", ['linha_id' => null])
            ->assertOk();

        $this->assertNull($estudante->refresh()->linha_id);
    }

    public function test_listagem_traz_ocupacao_e_vagas(): void
    {
        $this->linhaCom(capacidade: 10, ativos: 3);

        $linha = $this->getJson('/api/linha')->assertOk()->json('data.0');

        $this->assertSame(3, $linha['ocupacao']);
        $this->assertSame(7, $linha['vagas_restantes']);
    }

    public function test_nao_exclui_linha_com_estudantes_vinculados(): void
    {
        $linha = $this->linhaCom(capacidade: 10, ativos: 1);

        // Nao ha chave estrangeira em estudantes.linha_id: apagar deixaria o
        // estudante apontando para uma linha inexistente.
        $this->deleteJson("/api/linha/{$linha->id}")->assertStatus(409);

        $this->assertNotNull(Linha::find($linha->id));
    }

    public function test_exclui_linha_sem_estudantes(): void
    {
        $linha = Linha::factory()->create(['max_capacity' => 10]);

        $this->deleteJson("/api/linha/{$linha->id}")->assertOk();

        $this->assertNull(Linha::find($linha->id));
    }

    public function test_capacidade_precisa_ser_positiva(): void
    {
        $this->postJson('/api/linha', ['name' => 'Linha Teste', 'max_capacity' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors('max_capacity');
    }
}
