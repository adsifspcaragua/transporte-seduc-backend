<?php

namespace Database\Seeders\Support;

/**
 * Roteiro único dos dados de teste: cada pessoa tem uma inscrição e, quando a
 * inscrição foi aprovada (ou aprovada e depois recusada), um estudante.
 *
 * As seeders de inscrição, estudante e recadastro leem daqui, então ninguém
 * que está na lista de espera (Incompleto, Em analise, Em revisao) aparece
 * também como estudante.
 *
 * Colunas: nome, status da inscrição, estudante, recadastro do período aberto.
 *   estudante:  Ativo    -> status Aprovado, situação Ativo
 *               Inativo  -> status Aprovado, situação Inativo (perdeu o benefício)
 *               Recusado -> status Recusado, situação Inativo
 *   recadastro: Pendente | Em analise | Pendencia | Aprovado | Rejeitado,
 *               ou null para quem não iniciou (aparece em "ausentes").
 */
final class Cenarios
{
    private const TABELA = [
        // Estudantes ativos
        ['Ana Beatriz Conceição', 'Aprovado', 'Ativo', 'Aprovado'],
        ['João Pedro Araújo', 'Aprovado', 'Ativo', 'Aprovado'],
        ['Maria Eduarda Souza', 'Aprovado', 'Ativo', 'Em analise'],
        ['Lucas Gabriel Ferreira', 'Aprovado', 'Ativo', 'Em analise'],
        ['Júlia Helena Martins', 'Aprovado', 'Ativo', 'Pendencia'],
        ['Pedro Henrique Lima', 'Aprovado', 'Ativo', 'Pendente'],
        ['Larissa Cristina Gonçalves', 'Aprovado', 'Ativo', null],
        ['Gustavo Almeida Ribeiro', 'Aprovado', 'Ativo', 'Aprovado'],
        ['Beatriz Santos Oliveira', 'Aprovado', 'Ativo', 'Em analise'],
        ['Rafael Costa Pereira', 'Aprovado', 'Ativo', null],
        ['Camila Rodrigues Alves', 'Aprovado', 'Ativo', 'Aprovado'],
        ['Thiago Carvalho Nunes', 'Aprovado', 'Ativo', 'Pendencia'],
        ['Isabela Moreira Castro', 'Aprovado', 'Ativo', 'Aprovado'],
        ['Mateus Barbosa Rocha', 'Aprovado', 'Ativo', null],
        ['Letícia Fernandes Dias', 'Aprovado', 'Ativo', 'Em analise'],
        ['Felipe Teixeira Mendes', 'Aprovado', 'Ativo', 'Aprovado'],
        ['Yasmin Cardoso Pinto', 'Aprovado', 'Ativo', 'Pendente'],
        ['Bruno Azevedo Freitas', 'Aprovado', 'Ativo', 'Aprovado'],
        ['Sofia Ramos Correia', 'Aprovado', 'Ativo', null],
        ['Vinícius Monteiro Lopes', 'Aprovado', 'Ativo', 'Em analise'],

        // Aprovados que perderam o benefício
        ['Gabriela Nascimento Reis', 'Aprovado', 'Inativo', 'Rejeitado'],
        ['Leonardo Cavalcanti Melo', 'Aprovado', 'Inativo', null],
        ['Mariana Duarte Farias', 'Aprovado', 'Inativo', 'Rejeitado'],
        ['Eduardo Batista Moura', 'Aprovado', 'Inativo', null],

        // Inscrição recusada depois de o estudante já existir
        ['Carolina Vieira Campos', 'Rejeitado', 'Recusado', null],
        ['Diego Fontes Machado', 'Rejeitado', 'Recusado', null],

        // Inscrição recusada direto: nunca virou estudante
        ['Natália Siqueira Brandão', 'Rejeitado', null, null],
        ['Henrique Pacheco Viana', 'Rejeitado', null, null],

        // Lista de espera: completas, aguardando a análise
        ['Amanda Ribeiro Coelho', 'Em analise', null, null],
        ['Gabriel Antunes Prado', 'Em analise', null, null],
        ['Lívia Marques Torres', 'Em analise', null, null],
        ['Samuel Cunha Bezerra', 'Em analise', null, null],
        ['Helena Xavier Queiroz', 'Em analise', null, null],

        // Lista de espera: devolvidas para o estudante corrigir
        ['João da Silva', 'Em revisao', null, null],
        ['Rebeca Lacerda Pires', 'Em revisao', null, null],
        ['Otávio Guimarães Sales', 'Em revisao', null, null],

        // Lista de espera: cadastro ainda pela metade
        ['Clara Mendonça Rios', 'Incompleto', null, null],
        ['Arthur Peixoto Amaral', 'Incompleto', null, null],
        ['Manuela Tavares Neves', 'Incompleto', null, null],
        ['Caio Rezende Barros', 'Incompleto', null, null],
    ];

    /**
     * @return list<array{indice: int, nome: string, cpf: string, inscricao: string, estudante: ?string, recadastro: ?string}>
     */
    public static function pessoas(): array
    {
        return array_map(
            fn (array $linha, int $indice) => [
                'indice' => $indice,
                'nome' => $linha[0],
                'cpf' => self::cpf($indice),
                'inscricao' => $linha[1],
                'estudante' => $linha[2],
                'recadastro' => $linha[3],
            ],
            self::TABELA,
            array_keys(self::TABELA),
        );
    }

    /**
     * CPF válido e fixo por pessoa: a seeder pode rodar de novo sem duplicar.
     */
    public static function cpf(int $indice): string
    {
        $base = str_pad((string) (281734506 + $indice * 7654321), 9, '0', STR_PAD_LEFT);
        $base = substr($base, -9);

        foreach ([10, 11] as $peso) {
            $soma = 0;

            for ($i = 0; $i < $peso - 1; $i++) {
                $soma += (int) $base[$i] * ($peso - $i);
            }

            $resto = $soma % 11;
            $base .= $resto < 2 ? '0' : (string) (11 - $resto);
        }

        return $base;
    }
}
