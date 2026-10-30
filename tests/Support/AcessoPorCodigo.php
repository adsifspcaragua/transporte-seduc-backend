<?php

namespace Tests\Support;

use App\Services\Estudante\CodigoAcessoService;
use Illuminate\Testing\TestResponse;

/**
 * Passa pela etapa do codigo nos testes do que vem depois dela. O fluxo do
 * codigo em si e testado em Feature/Estudante/CodigoAcessoTest.
 */
trait AcessoPorCodigo
{
    /**
     * @param  string  $rota  A rota da primeira etapa; o codigo vai em "{rota}/verificar".
     */
    protected function acessarComCodigo(string $rota, string $cpf): TestResponse
    {
        return $this->postJson("{$rota}/verificar", [
            'cpf' => $cpf,
            'codigo' => app(CodigoAcessoService::class)->gerar($cpf),
        ]);
    }
}
