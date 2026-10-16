<?php

namespace Database\Seeders;

use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Models\InscricaoInstituicoes;
use App\Models\Instituicao;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\Support\Cenarios;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InscricaoSeeder extends Seeder
{
    private const ENDERECOS = [
        ['Rua Santa Cruz', 'Centro', '11660040'],
        ['Avenida da Praia', 'Martim de Sá', '11662000'],
        ['Rua das Palmeiras', 'Indaiá', '11665000'],
        ['Rua Projetada', 'Porto Novo', '11667000'],
        ['Rua Benedito Zacarias', 'Travessão', '11668200'],
        ['Avenida José Herculano', 'Massaguaçu', '11674000'],
        ['Rua Sebastião Mariano Nepomuceno', 'Olaria', '11661000'],
        ['Rua Jorge Burihan', 'Sumaré', '11661220'],
    ];

    private const CURSOS = [
        'Administração', 'Ciência da Computação', 'Direito', 'Enfermagem',
        'Engenharia Civil', 'Engenharia de Software', 'Fisioterapia', 'Pedagogia',
        'Psicologia', 'Sistemas de Informação', 'Arquitetura e Urbanismo', 'Educação Física',
    ];

    private const BOLSAS = ['Bolsa integral', '75%', '50%', '25%'];

    /** Motivo e campos apontados em cada devolução para correção. */
    private const REVISOES = [
        ['O nome não confere com o documento de identidade.', ['name'], ['identidade']],
        ['Comprovante de residência ilegível e número do endereço ausente.', ['number', 'cep'], ['residencia']],
        ['Declaração de matrícula de semestre anterior.', [], ['matricula']],
    ];

    private const RECUSAS = [
        'Estudante reside fora do município.',
        'Curso não atendido pelo transporte universitário.',
        'Documentação inconsistente com os dados informados.',
        'Já possui benefício de transporte de outro programa.',
    ];

    private ?string $pdfDeTeste = null;

    public function run(): void
    {
        $instituicoes = Instituicao::orderBy('id')->pluck('id')->values();

        foreach (Cenarios::pessoas() as $pessoa) {
            $inscricao = $this->inscricao($pessoa);

            $this->dadosInstitucionais($inscricao, $pessoa, $instituicoes);
            $this->documentos($inscricao, $pessoa);
        }
    }

    /**
     * @param  array<string, mixed>  $pessoa
     */
    private function inscricao(array $pessoa): Inscricao
    {
        $i = $pessoa['indice'];
        [$rua, $bairro, $cep] = self::ENDERECOS[$i % count(self::ENDERECOS)];
        $sobrenome = Str::afterLast($pessoa['nome'], ' ');
        $incompleta = $pessoa['inscricao'] === 'Incompleto';
        $revisao = $pessoa['inscricao'] === 'Em revisao'
            ? self::REVISOES[$i % count(self::REVISOES)]
            : null;

        return Inscricao::updateOrCreate(
            ['cpf' => $pessoa['cpf']],
            [
                'name' => $pessoa['nome'],
                'rg' => (string) (300000000 + $i * 104729),
                'birth_date' => sprintf('%d-%02d-%02d', 1999 + $i % 7, $i % 12 + 1, $i % 27 + 1),
                'phone' => '1298'.str_pad((string) (1234567 + $i * 1379), 7, '0', STR_PAD_LEFT),
                'email' => Str::slug(Str::before($pessoa['nome'], ' ').' '.$sobrenome, '.').$i.'@example.com',
                'cep' => $cep,
                'address' => $rua,
                'neighborhood' => $bairro,
                'city' => 'Caraguatatuba',
                // Incompleta de propósito: sem número nem nome da mãe.
                'number' => $incompleta ? null : (string) ($i * 37 % 900 + 10),
                'complement' => $i % 5 === 0 ? 'Apto '.($i + 11) : null,
                'father_name' => $i % 4 === 0 ? null : 'José '.$sobrenome,
                'mother_name' => $incompleta ? null : 'Maria '.$sobrenome,
                'observation' => match ($pessoa['inscricao']) {
                    'Em revisao' => $revisao[0],
                    'Rejeitado' => self::RECUSAS[$i % count(self::RECUSAS)],
                    default => null,
                },
                'campos_pendentes' => $revisao[1] ?? null,
                'status' => $pessoa['inscricao'],
                'accepted_terms' => ! $incompleta,
                'accepted_terms_2' => ! $incompleta,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pessoa
     * @param  Collection<int, int>  $instituicoes
     */
    private function dadosInstitucionais(Inscricao $inscricao, array $pessoa, $instituicoes): void
    {
        $i = $pessoa['indice'];
        $bolsista = $i % 4 === 1;

        InscricaoInstituicoes::updateOrCreate(
            ['inscricao_id' => $inscricao->id],
            [
                'course' => self::CURSOS[$i % count(self::CURSOS)],
                'semester' => (string) ($i % 8 + 1),
                'expected_completion' => now()->addYears($i % 4 + 1)->endOfYear()->toDateString(),
                'instituicao_id' => $instituicoes[$i % $instituicoes->count()],
                'shift' => $i % 3 === 0 ? 1 : 2,
                'city_destination' => InscricaoInstituicoes::CIDADE_DESTINO,
                'used_transport' => $i % 3 !== 2,
                'days_of_week' => [[1, 2, 3, 4, 5], [1, 3, 5], [2, 4], [1, 2, 3, 4], [6]][$i % 5],
                'has_scholarship' => $bolsista,
                'scholarship_type' => $bolsista ? self::BOLSAS[$i % count(self::BOLSAS)] : null,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pessoa
     */
    private function documentos(Inscricao $inscricao, array $pessoa): void
    {
        $nomes = InscricaoDocumento::OBRIGATORIOS;

        // Quem ainda está preenchendo só enviou parte dos documentos.
        if ($pessoa['inscricao'] === 'Incompleto') {
            $nomes = array_slice($nomes, 0, $pessoa['indice'] % 3 + 1);
        }

        $paraReenviar = $pessoa['inscricao'] === 'Em revisao'
            ? self::REVISOES[$pessoa['indice'] % count(self::REVISOES)][2]
            : [];

        foreach ($nomes as $nome) {
            $caminho = "inscricoes/{$inscricao->id}/{$nome}.pdf";
            Storage::put($caminho, $this->pdfDeTeste());

            $inscricao->inscricao_documentos()->updateOrCreate(
                ['name' => $nome],
                [
                    'type' => 'application/pdf',
                    'file_path' => $caminho,
                    'nome_original' => "{$nome}.pdf",
                    'status' => match ($pessoa['inscricao']) {
                        'Aprovado' => 'Aprovado',
                        'Rejeitado' => 'Rejeitado',
                        default => in_array($nome, $paraReenviar, true) ? 'Rejeitado' : 'Em analise',
                    },
                ],
            );
        }
    }

    private function pdfDeTeste(): string
    {
        return $this->pdfDeTeste ??= Pdf::loadHTML(
            '<h1 style="font-family: Helvetica">Documento de teste</h1>'
            .'<p style="font-family: Helvetica">Arquivo gerado pela seeder para testar a visualização de documentos.</p>'
        )->output();
    }
}
