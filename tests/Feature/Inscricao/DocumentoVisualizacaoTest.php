<?php

namespace Tests\Feature\Inscricao;

use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da visualizacao do documento pela responsavel.
 *
 * "Visualizar" abre o arquivo na aba; "Baixar" salva. A diferenca esta no
 * cabecalho Content-Disposition, e nao no link: o navegador obedece o cabecalho
 * mesmo com target="_blank".
 *
 * Exibir na aba so vale para o que o navegador mostra sem executar script. SVG
 * e XML e pode carregar script, que rodaria no dominio da API se fosse servido
 * inline, entao continua sendo anexo.
 */
class DocumentoVisualizacaoTest extends TestCase
{
    use RefreshDatabase;

    /** Cria a inscricao com um documento do tipo pedido e devolve [inscricao, documento]. */
    private function criarDocumento(UploadedFile $arquivo): array
    {
        Storage::fake('local');

        $inscricao = Inscricao::factory()->create(['access_token' => Str::random(64)]);
        $caminho = $arquivo->store('documentos');

        $documento = InscricaoDocumento::create([
            'name' => 'identidade',
            'type' => 'imagem',
            'file_path' => $caminho,
            'nome_original' => $arquivo->getClientOriginalName(),
            'status' => 'Em analise',
            'inscricao_id' => $inscricao->id,
        ]);

        return [$inscricao, $documento];
    }

    private function url(Inscricao $inscricao, InscricaoDocumento $documento, bool $inline): string
    {
        $url = "/api/inscricoes/{$inscricao->id}/documentos/{$documento->id}/download";

        return $inline ? "{$url}?inline=1" : $url;
    }

    public function test_visualizar_uma_imagem_abre_na_aba(): void
    {
        [$inscricao, $documento] = $this->criarDocumento(
            UploadedFile::fake()->image('rg.png'),
        );

        $resposta = $this->get($this->url($inscricao, $documento, true), [
            'X-Inscricao-Token' => $inscricao->access_token,
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->assertStringStartsWith(
            'inline',
            $resposta->headers->get('Content-Disposition'),
        );
    }

    public function test_baixar_continua_salvando_o_arquivo(): void
    {
        [$inscricao, $documento] = $this->criarDocumento(
            UploadedFile::fake()->image('rg.png'),
        );

        $resposta = $this->get($this->url($inscricao, $documento, false), [
            'X-Inscricao-Token' => $inscricao->access_token,
        ])->assertOk();

        $this->assertStringStartsWith(
            'attachment',
            $resposta->headers->get('Content-Disposition'),
        );
    }

    public function test_svg_nao_e_exibido_na_aba_mesmo_pedindo_inline(): void
    {
        [$inscricao, $documento] = $this->criarDocumento(
            UploadedFile::fake()->createWithContent(
                'malicioso.svg',
                '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
            ),
        );

        $resposta = $this->get($this->url($inscricao, $documento, true), [
            'X-Inscricao-Token' => $inscricao->access_token,
        ])->assertOk();

        $this->assertStringStartsWith(
            'attachment',
            $resposta->headers->get('Content-Disposition'),
        );
    }
}
