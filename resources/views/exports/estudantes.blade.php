<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Estudantes</title>
    <style>
        @page {
            margin: 118px 32px 54px 32px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #0f172a;
            margin: 0;
        }

        /* Cabeçalho e rodapé fixos se repetem em todas as páginas. */
        .cabecalho {
            position: fixed;
            top: -96px;
            left: 0;
            right: 0;
            background-color: #084e80;
            border-radius: 8px;
            color: #ffffff;
        }

        .cabecalho table {
            width: 100%;
            border-collapse: collapse;
        }

        .cabecalho td {
            padding: 16px 22px;
            vertical-align: middle;
        }

        .cabecalho .sobretitulo {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: #c4e6f0;
        }

        .cabecalho .titulo {
            font-size: 20px;
            font-weight: bold;
            margin-top: 3px;
        }

        .cabecalho .orgao {
            text-align: right;
            font-size: 9px;
            line-height: 1.5;
            color: #cfeafe;
        }

        .cabecalho .orgao strong {
            color: #ffffff;
            font-size: 10px;
        }

        .rodape {
            position: fixed;
            bottom: -34px;
            left: 0;
            right: 0;
            height: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8px;
            color: #64748b;
        }

        /* Resumo */
        .resumo {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .resumo td {
            width: 16.66%;
            padding: 0 4px;
        }

        .resumo td:first-child { padding-left: 0; }
        .resumo td:last-child { padding-right: 0; }

        .resumo .card {
            background-color: #f1f5f9;
            border-left: 4px solid #084e80;
            border-radius: 0 6px 6px 0;
            padding: 9px 12px;
        }

        .resumo .rotulo {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #64748b;
        }

        .resumo .valor {
            font-size: 18px;
            font-weight: bold;
            margin-top: 2px;
            color: #0f172a;
        }

        .resumo .card.sucesso { border-left-color: #008000; }
        .resumo .card.neutro { border-left-color: #94a3b8; }
        .resumo .card.alerta { border-left-color: #d97706; }
        .resumo .card.perigo { border-left-color: #d32f2f; }

        /* Tabela */
        .estudantes {
            width: 100%;
            border-collapse: collapse;
        }

        .estudantes thead th {
            background-color: #042c49;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            text-align: left;
            padding: 8px 7px;
        }

        .estudantes tbody tr {
            page-break-inside: avoid;
        }

        .estudantes tbody td {
            padding: 7px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .estudantes tbody tr.par td {
            background-color: #f8fafc;
        }

        .estudantes .indice {
            color: #94a3b8;
            text-align: right;
        }

        .estudantes .nome {
            font-weight: bold;
            font-size: 9.5px;
        }

        .estudantes .secundario {
            color: #64748b;
            font-size: 8px;
            margin-top: 1px;
        }

        .estudantes .mono {
            white-space: nowrap;
        }

        .instituicao {
            display: inline-block;
            background-color: #c4e6f0;
            color: #063b61;
            font-weight: bold;
            font-size: 8px;
            border-radius: 4px;
            padding: 2px 6px;
        }

        .sem-linha {
            color: #94a3b8;
            font-style: italic;
        }

        .badge {
            display: inline-block;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 4px;
            padding: 3px 7px;
            white-space: nowrap;
        }

        .badge.sucesso { background-color: #e3f1e3; color: #006b00; }
        .badge.perigo { background-color: #fbe5e5; color: #b91c1c; }
        .badge.alerta { background-color: #fef3c7; color: #b45309; }
        .badge.neutro { background-color: #e2e8f0; color: #475569; }

        .vazio {
            text-align: center;
            padding: 40px 0;
            color: #64748b;
            font-size: 11px;
        }
    </style>
</head>
<body>
@php
    $tons = [
        'Aprovado' => 'sucesso',
        'Ativo' => 'sucesso',
        'Recusado' => 'perigo',
        'Lista de espera' => 'alerta',
        'Inativo' => 'neutro',
    ];
@endphp

<div class="cabecalho">
    <table>
        <tr>
            <td>
                <div class="sobretitulo">Transporte Universitário</div>
                <div class="titulo">Relatório de Estudantes</div>
            </td>
            <td class="orgao">
                <strong>Prefeitura de Caraguatatuba</strong><br>
                Secretaria Municipal de Educação<br>
                Gerado em {{ $geradoEm->format('d/m/Y') }} às {{ $geradoEm->format('H:i') }}
            </td>
        </tr>
    </table>
</div>

<div class="rodape">
    Transporte Universitário · Secretaria Municipal de Educação de Caraguatatuba
</div>

<table class="resumo">
    <tr>
        <td>
            <div class="card">
                <div class="rotulo">Total</div>
                <div class="valor">{{ $resumo['total'] }}</div>
            </div>
        </td>
        <td>
            <div class="card sucesso">
                <div class="rotulo">Ativos</div>
                <div class="valor">{{ $resumo['ativos'] }}</div>
            </div>
        </td>
        <td>
            <div class="card neutro">
                <div class="rotulo">Inativos</div>
                <div class="valor">{{ $resumo['inativos'] }}</div>
            </div>
        </td>
        <td>
            <div class="card sucesso">
                <div class="rotulo">Aprovados</div>
                <div class="valor">{{ $resumo['aprovados'] }}</div>
            </div>
        </td>
        <td>
            <div class="card alerta">
                <div class="rotulo">Lista de espera</div>
                <div class="valor">{{ $resumo['lista_de_espera'] }}</div>
            </div>
        </td>
        <td>
            <div class="card perigo">
                <div class="rotulo">Recusados</div>
                <div class="valor">{{ $resumo['recusados'] }}</div>
            </div>
        </td>
    </tr>
</table>

<table class="estudantes">
    <thead>
        <tr>
            <th style="width: 3%;">#</th>
            <th style="width: 22%;">Estudante</th>
            <th style="width: 10%;">CPF</th>
            <th style="width: 10%;">Telefone</th>
            <th style="width: 15%;">Instituição</th>
            <th style="width: 15%;">Curso / Semestre</th>
            <th style="width: 9%;">Linha</th>
            <th style="width: 8%;">Status</th>
            <th style="width: 8%;">Situação</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($estudantes as $estudante)
            <tr class="{{ $loop->even ? 'par' : '' }}">
                <td class="indice">{{ $loop->iteration }}</td>
                <td>
                    <div class="nome">{{ $estudante['nome'] }}</div>
                    <div class="secundario">{{ $estudante['email'] ?: 'Email não informado' }}</div>
                </td>
                <td class="mono">{{ $estudante['cpf'] ?: '—' }}</td>
                <td class="mono">{{ $estudante['telefone'] ?: '—' }}</td>
                <td>
                    @if ($estudante['instituicao'])
                        <span class="instituicao">{{ $estudante['instituicao'] }}</span>
                    @else
                        <span class="sem-linha">Não informada</span>
                    @endif
                </td>
                <td>
                    <div>{{ $estudante['curso'] ?: 'Curso não informado' }}</div>
                    <div class="secundario">
                        {{ $estudante['semestre'] ?: 'Semestre não informado' }}{{ $estudante['turno'] ? ' · '.$estudante['turno'] : '' }}
                    </div>
                </td>
                <td class="{{ $estudante['linha'] === 'Sem linha' ? 'sem-linha' : '' }}">{{ $estudante['linha'] }}</td>
                <td>
                    <span class="badge {{ $tons[$estudante['status']] ?? 'neutro' }}">{{ $estudante['status'] ?: 'Sem status' }}</span>
                </td>
                <td>
                    <span class="badge {{ $tons[$estudante['situacao']] ?? 'neutro' }}">{{ $estudante['situacao'] ?: '—' }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="vazio">Nenhum estudante cadastrado.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
