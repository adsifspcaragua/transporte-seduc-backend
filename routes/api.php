<?php

use App\Http\Controllers\Api\Auditoria\AuditoriaController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Curso\CursoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Dispositivo\AnaliseDispositivoController;
use App\Http\Controllers\Api\Dispositivo\MeuDispositivoController;
use App\Http\Controllers\Api\Estudante\AcessoEstudanteController;
use App\Http\Controllers\Api\Estudante\Documento\InscricaoDocumentoController;
use App\Http\Controllers\Api\Estudante\EstudanteController;
use App\Http\Controllers\Api\Estudante\InscricaoController;
use App\Http\Controllers\Api\Estudante\InscricaoInstituicaoController;
use App\Http\Controllers\Api\Frequencia\ChamadaController;
use App\Http\Controllers\Api\Frequencia\JustificativaController;
use App\Http\Controllers\Api\Frequencia\RelatorioFrequenciaController;
use App\Http\Controllers\Api\Instituicao\InstituicaoController;
use App\Http\Controllers\Api\LinhaController;
use App\Http\Controllers\Api\Presenca\PresencaController;
use App\Http\Controllers\Api\Presenca\RegistroPresencaController;
use App\Http\Controllers\Api\Reecadastro\AusentesReecadastroController;
use App\Http\Controllers\Api\Reecadastro\DocumentoReecadastroController;
use App\Http\Controllers\Api\Reecadastro\PeriodoReecadastroController;
use App\Http\Controllers\Api\Reecadastro\ReecadastroPublicoController;
use App\Http\Controllers\Api\Reecadastro\SolicitacaoReecadastroController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Viagem\AutorizacaoLinhaController;
use App\Http\Controllers\Api\Viagem\PontoEmbarqueController;
use App\Http\Controllers\Api\Viagem\ProgramacaoController;
use App\Http\Controllers\Api\Viagem\VeiculoController;
use App\Http\Controllers\Api\Viagem\ViagemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;


Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/token', [AuthController::class, 'tokenLogin'])->middleware('throttle:5,1');

Route::post('/password/email', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

// Leitura pública: sem login, mas dentro do escopo stateful do Sanctum.
// Só há métodos GET aqui, que não passam por CSRF, então o estudante com token
// segue funcionando — e a sessão continua sendo carregada, que é o que permite
// ao administrador logado ser reconhecido em vez de cair na exigência do token.
Route::get('instituicao', [InstituicaoController::class, 'index']);
Route::get('curso', [CursoController::class, 'index']);

Route::middleware('inscricao.token')->group(function () {
    Route::get('inscricoes/{inscricao}', [InscricaoController::class, 'show']);
    Route::get('inscricoes/{inscricao}/documentos', [InscricaoDocumentoController::class, 'index']);
    Route::get('inscricoes/{inscricao}/documentos/{documento}/download', [InscricaoDocumentoController::class, 'download']);
});

// Escrita pública do estudante: sem login e sem sessão. Fica fora do escopo
// stateful do Sanctum para que o CSRF não seja exigido de quem nunca recebeu um
// cookie. A proteção é o throttle e o token devolvido na criação.
//
// Só o estudante escreve por aqui. O administrador usa as rotas autenticadas
// mais abaixo; se algum dia precisar de uma destas, ela terá de voltar para o
// escopo stateful, senão a sessão dele não será lida e o token será exigido.
Route::withoutMiddleware(EnsureFrontendRequestsAreStateful::class)->group(function () {
    Route::post('inscricoes/validar-step', [InscricaoController::class, 'validateStep']);
    Route::post('inscricoes', [InscricaoController::class, 'store'])->middleware('throttle:10,1');
    Route::post('area-estudante/acesso', AcessoEstudanteController::class)->middleware('throttle:10,1');

    // Lista de espera: o estudante nao faz login, entao cada inscrição só é
    // acessível com o token devolvido na criação.
    Route::middleware('inscricao.token')->group(function () {
        Route::put('inscricoes/{inscricao}', [InscricaoController::class, 'update']);
        Route::patch('inscricoes/{inscricao}', [InscricaoController::class, 'update']);
        Route::post('inscricoes/{inscricao_id}/instituicoes', [InscricaoInstituicaoController::class, 'store']);
        Route::put('inscricoes/{inscricao_id}/instituicoes/{instituicao}', [InscricaoInstituicaoController::class, 'update']);
        Route::patch('inscricoes/{inscricao_id}/instituicoes/{instituicao}', [InscricaoInstituicaoController::class, 'update']);
        Route::post('inscricoes/{inscricao}/documentos', [InscricaoDocumentoController::class, 'store']);
        Route::delete('inscricoes/{inscricao}/documentos/{documento}', [InscricaoDocumentoController::class, 'destroy']);
    });

    // Recadastro do estudante: acesso pelo CPF, sem login, com token de sessão.
    Route::post('reecadastro/consulta', [ReecadastroPublicoController::class, 'consulta'])->middleware('throttle:10,1');
    Route::post('reecadastro/solicitacoes/{solicitacao}/documentos', [ReecadastroPublicoController::class, 'documento'])->middleware('throttle:30,1');
    Route::put('reecadastro/solicitacoes/{solicitacao}/dados', [ReecadastroPublicoController::class, 'dados'])->middleware('throttle:10,1');
    Route::post('reecadastro/solicitacoes/{solicitacao}/finalizar', [ReecadastroPublicoController::class, 'finalizar'])->middleware('throttle:10,1');
});

Route::middleware('auth:sanctum')->group(function () {
    /**
     * Exibir usuario autenticado.
     *
     * Retorna os dados do usuario autenticado na requisicao atual.
     *
     * @group Autenticacao
     *
     * @authenticated
     */
    Route::get('/me', function (Request $request) {
        try {
            $user = $request->user();

            return response()->json([
                ...$user->toArray(),
                'roles' => $user->roleTitles(),
                'permissions' => $user->permissionTitles(),
            ]);
        } catch (Exception $e) {
            return response()->json('Falha ao retornar usuário', 401);
        }
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Tela inicial: contadores agregados, sem dado individual, entao basta estar
    // autenticado. Cada numero leva para a tela que ja tem sua propria permissao.
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/auth/token/revoke', [AuthController::class, 'tokenLogout']);

    // A seleção expõe somente id e nome de motoristas ativos a quem gerencia linhas.
    Route::get('users/motoristas', [UserController::class, 'motoristas'])->middleware('permission:linhas.write');

    // Usuários (apenas admin)
    Route::apiResource('users', UserController::class)->only(['index', 'show'])->middleware('permission:users.view');
    Route::apiResource('users', UserController::class)->only(['store', 'update'])->middleware('permission:users.write');
    Route::apiResource('users', UserController::class)->only(['destroy'])->middleware('permission:users.delete');
    Route::patch('users/{user}/inativar', [UserController::class, 'inativar'])->middleware('permission:users.write');
    Route::patch('users/{user}/ativar', [UserController::class, 'ativar'])->middleware('permission:users.write');

    // Cargos (apenas admin)
    Route::apiResource('roles', RoleController::class)->only(['index', 'show'])->middleware('permission:roles.view');
    Route::apiResource('roles', RoleController::class)->only(['store', 'update'])->middleware('permission:roles.write');
    Route::apiResource('roles', RoleController::class)->only(['destroy'])->middleware('permission:roles.delete');

    // Estudantes
    Route::apiResource('estudantes', EstudanteController::class)->only(['index', 'show'])->middleware('permission:estudantes.view');
    Route::apiResource('estudantes', EstudanteController::class)->only(['store', 'update'])->middleware('permission:estudantes.write');
    Route::apiResource('estudantes', EstudanteController::class)->only(['destroy'])->middleware('permission:estudantes.delete');
    Route::get('contar-estudantes', [EstudanteController::class, 'countEstudantes'])->middleware('permission:estudantes.view');
    Route::get('exportar-estudantes/{type}', [EstudanteController::class, 'exportarEstudantes'])->middleware('permission:estudantes.view');

    // Inscrições (área administrativa)
    Route::put('inscricoes/analise/{id}', [InscricaoController::class, 'analise'])->middleware('permission:inscricoes.analise');
    Route::apiResource('inscricoes', InscricaoController::class)
        ->only(['index'])->parameters(['inscricoes' => 'inscricao'])->middleware('permission:inscricoes.view');
    Route::apiResource('inscricoes', InscricaoController::class)
        ->only(['destroy'])->parameters(['inscricoes' => 'inscricao'])->middleware('permission:inscricoes.delete');
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class)
        ->only(['index', 'show'])->parameters(['instituicoes' => 'instituicao'])->middleware('permission:inscricoes.view');
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class)
        ->only(['destroy'])->parameters(['instituicoes' => 'instituicao'])->middleware('permission:inscricoes.delete');
    // Instituições (index é público; aqui apenas leitura individual/escrita/remoção)
    Route::apiResource('instituicao', InstituicaoController::class)->only(['show', 'store', 'update'])->middleware('permission:instituicoes.write');
    Route::apiResource('instituicao', InstituicaoController::class)->only(['destroy'])->middleware('permission:instituicoes.delete');

    // Linhas
    Route::get('linha/{linha}/estudantes', [LinhaController::class, 'estudantes'])
        ->middleware(['permission:linhas.view', 'permission:estudantes.view']);
    Route::apiResource('linha', LinhaController::class)->only(['index', 'show'])->middleware('permission:linhas.view');
    Route::apiResource('linha', LinhaController::class)->only(['store', 'update'])->middleware('permission:linhas.write');
    Route::apiResource('linha', LinhaController::class)->only(['destroy'])->middleware('permission:linhas.delete');

    // Períodos de recadastro
    Route::apiResource('reecadastro/periodos', PeriodoReecadastroController::class)
        ->only(['index', 'show'])->parameters(['periodos' => 'periodo'])->middleware('permission:periodos.view');
    Route::apiResource('reecadastro/periodos', PeriodoReecadastroController::class)
        ->only(['store', 'update'])->parameters(['periodos' => 'periodo'])->middleware('permission:periodos.write');
    Route::apiResource('reecadastro/periodos', PeriodoReecadastroController::class)
        ->only(['destroy'])->parameters(['periodos' => 'periodo'])->middleware('permission:periodos.delete');
    // Quem esta ativo e nao concluiu o recadastro do periodo.
    //
    // Ver exige apenas leitura. Inativar corta o transporte de varios estudantes
    // de uma vez, entao pede `periodos.write` (gestor/admin) e nao
    // `estudantes.write`: o operador edita um estudante por vez, mas nao deve
    // conseguir desativar uma turma inteira em um clique.
    Route::get('reecadastro/periodos/{periodo}/ausentes', [AusentesReecadastroController::class, 'index'])->middleware('permission:periodos.view');
    Route::post('reecadastro/periodos/{periodo}/inativar-ausentes', [AusentesReecadastroController::class, 'inativar'])->middleware('permission:periodos.write');

    Route::patch('reecadastro/periodos/{periodo}/abrir', [PeriodoReecadastroController::class, 'abrir'])->middleware('permission:periodos.write');
    Route::patch('reecadastro/periodos/{periodo}/fechar', [PeriodoReecadastroController::class, 'fechar'])->middleware('permission:periodos.write');

    // Solicitações de recadastro (homologação)
    Route::put('reecadastro/solicitacoes/{solicitacao}/analise', [SolicitacaoReecadastroController::class, 'analise'])->middleware('permission:solicitacoes.analise');
    Route::apiResource('reecadastro/solicitacoes', SolicitacaoReecadastroController::class)
        ->only(['index', 'show'])->parameters(['solicitacoes' => 'solicitacao'])->middleware('permission:solicitacoes.view');
    Route::apiResource('reecadastro/solicitacoes', SolicitacaoReecadastroController::class)
        ->only(['destroy'])->parameters(['solicitacoes' => 'solicitacao'])->middleware('permission:solicitacoes.delete');

    // Documentos do recadastro
    Route::get('reecadastro/documentos/{documento}/download', [DocumentoReecadastroController::class, 'download'])->middleware('permission:documentos.view');
    Route::apiResource('reecadastro/documentos', DocumentoReecadastroController::class)
        ->only(['index', 'show'])->parameters(['documentos' => 'documento'])->middleware('permission:documentos.view');
    Route::apiResource('reecadastro/documentos', DocumentoReecadastroController::class)
        ->only(['destroy'])->parameters(['documentos' => 'documento'])->middleware('permission:documentos.delete');

    // Frequencia: a chamada diaria do motorista.
    //
    // A permissao da rota diz o que o perfil pode fazer; em quais linhas, quem
    // decide e o EscopoFrequencia. O motorista so alcanca as linhas que conduz,
    // mesmo trocando o id na URL; `frequencias.todas` libera todas.
    Route::get('frequencias/linhas', [ChamadaController::class, 'linhas'])->middleware('permission:frequencias.view');
    Route::get('frequencias/relatorio', [RelatorioFrequenciaController::class, 'index'])->middleware('permission:frequencias.view');
    Route::get('frequencias/estudantes/{estudante}/relatorio', [RelatorioFrequenciaController::class, 'estudante'])->middleware('permission:frequencias.view');
    Route::apiResource('frequencias/chamadas', ChamadaController::class)
        ->only(['index', 'show'])->parameters(['chamadas' => 'chamada'])->middleware('permission:frequencias.view');
    Route::apiResource('frequencias/chamadas', ChamadaController::class)
        ->only(['store', 'update'])->parameters(['chamadas' => 'chamada'])->middleware('permission:frequencias.write');
    Route::patch('frequencias/chamadas/{chamada}/fechar', [ChamadaController::class, 'fechar'])->middleware('permission:frequencias.write');
    Route::patch('frequencias/chamadas/{chamada}/reabrir', [ChamadaController::class, 'reabrir'])->middleware('permission:frequencias.write');
    Route::apiResource('frequencias/chamadas', ChamadaController::class)
        ->only(['destroy'])->parameters(['chamadas' => 'chamada'])->middleware('permission:frequencias.delete');

    // Justificativas de falta. Quem lanca a chamada pode justificar uma falta;
    // so a responsavel ve a fila e decide.
    Route::post('frequencias/justificativas', [JustificativaController::class, 'store'])->middleware('permission:frequencias.write');
    Route::put('frequencias/justificativas/{justificativa}/analise', [JustificativaController::class, 'analise'])->middleware('permission:justificativas.analise');
    Route::get('frequencias/justificativas', [JustificativaController::class, 'index'])->middleware('permission:justificativas.view');
    Route::get('frequencias/justificativas/{justificativa}', [JustificativaController::class, 'show'])->middleware('permission:justificativas.view');

    // Presenca registrada pelo proprio estudante: QR fixo da van, navegador
    // autorizado, horario e geocerca (ROADMAP.md). Nao passa pelo motorista.
    //
    // O estudante sai da conta autenticada (middleware `estudante`), nunca da
    // URL ou do corpo da requisicao.
    Route::middleware(['permission:presencas.registrar', 'estudante'])->group(function () {
        Route::get('me/dispositivo', [MeuDispositivoController::class, 'show']);
        Route::middleware('throttle:dispositivo')->group(function () {
            Route::post('me/dispositivo/solicitacoes', [MeuDispositivoController::class, 'solicitar']);
            Route::delete('me/dispositivo/solicitacoes/{solicitacao}', [MeuDispositivoController::class, 'cancelar']);
            Route::post('me/dispositivo/solicitacoes/{solicitacao}/desafio', [MeuDispositivoController::class, 'desafio']);
            Route::post('me/dispositivo/solicitacoes/{solicitacao}/concluir', [MeuDispositivoController::class, 'concluir']);
        });

        Route::get('me/viagens', [RegistroPresencaController::class, 'viagens']);
        Route::get('me/presencas', [RegistroPresencaController::class, 'presencas']);
        Route::get('me/presencas/tentativas', [RegistroPresencaController::class, 'tentativas']);

        Route::middleware('throttle:presenca')->group(function () {
            Route::post('me/presencas/tentativas/{tentativa}/revisao', [RegistroPresencaController::class, 'pedirRevisao']);
            Route::post('presencas/resolver-qr', [RegistroPresencaController::class, 'resolverQr']);
            Route::post('presencas/desafios', [RegistroPresencaController::class, 'desafio']);
            Route::post('presencas', [RegistroPresencaController::class, 'confirmar']);
        });
    });

    // Vans, QR fixo e atribuicao van x linha. As rotas de atribuicao vem antes
    // de `veiculos/{veiculo}` para "atribuicoes" nao ser lido como id.
    Route::get('veiculos/atribuicoes', [VeiculoController::class, 'atribuicoes'])->middleware('permission:veiculos.view');
    Route::post('veiculos/atribuicoes', [VeiculoController::class, 'atribuir'])->middleware('permission:veiculos.write');
    Route::match(['put', 'patch'], 'veiculos/atribuicoes/{atribuicao}', [VeiculoController::class, 'atualizarAtribuicao'])->middleware('permission:veiculos.write');
    Route::delete('veiculos/atribuicoes/{atribuicao}', [VeiculoController::class, 'removerAtribuicao'])->middleware('permission:veiculos.write');
    Route::post('veiculos/{veiculo}/qr', [VeiculoController::class, 'rotacionarQr'])->middleware('permission:veiculos.write');
    Route::apiResource('veiculos', VeiculoController::class)->only(['index', 'show'])->middleware('permission:veiculos.view');
    Route::apiResource('veiculos', VeiculoController::class)->only(['store', 'update'])->middleware('permission:veiculos.write');
    Route::apiResource('veiculos', VeiculoController::class)->only(['destroy'])->middleware('permission:veiculos.delete');

    // Programacao: horarios de ida/volta, dias sem operacao e viagens datadas.
    Route::get('horarios-viagem', [ProgramacaoController::class, 'horarios'])->middleware('permission:viagens.view');
    Route::get('horarios-viagem/{horario}', [ProgramacaoController::class, 'horario'])->middleware('permission:viagens.view');
    Route::post('horarios-viagem', [ProgramacaoController::class, 'criarHorario'])->middleware('permission:viagens.write');
    Route::match(['put', 'patch'], 'horarios-viagem/{horario}', [ProgramacaoController::class, 'atualizarHorario'])->middleware('permission:viagens.write');
    Route::delete('horarios-viagem/{horario}', [ProgramacaoController::class, 'removerHorario'])->middleware('permission:viagens.write');
    Route::get('dias-sem-operacao', [ProgramacaoController::class, 'diasSemOperacao'])->middleware('permission:viagens.view');
    Route::post('dias-sem-operacao', [ProgramacaoController::class, 'criarDiaSemOperacao'])->middleware('permission:viagens.write');
    Route::delete('dias-sem-operacao/{dia}', [ProgramacaoController::class, 'removerDiaSemOperacao'])->middleware('permission:viagens.write');
    Route::post('viagens/gerar', [ViagemController::class, 'gerar'])->middleware('permission:viagens.write');
    Route::get('viagens', [ViagemController::class, 'index'])->middleware('permission:viagens.view');
    Route::get('viagens/{viagem}', [ViagemController::class, 'show'])->middleware('permission:viagens.view');
    Route::match(['put', 'patch'], 'viagens/{viagem}', [ViagemController::class, 'update'])->middleware('permission:viagens.write');

    // Remanejamento temporario de linha: e cadastro do estudante, entao segue
    // as permissoes de estudante.
    Route::get('autorizacoes-linha', [AutorizacaoLinhaController::class, 'index'])->middleware('permission:estudantes.view');
    Route::post('autorizacoes-linha', [AutorizacaoLinhaController::class, 'store'])->middleware('permission:estudantes.write');
    Route::delete('autorizacoes-linha/{autorizacao}', [AutorizacaoLinhaController::class, 'destroy'])->middleware('permission:estudantes.write');

    // Geocercas: pontos fixos de embarque e em quais linhas/sentidos valem.
    Route::get('pontos-embarque/linhas', [PontoEmbarqueController::class, 'vinculos'])->middleware('permission:geocercas.view');
    Route::post('pontos-embarque/linhas', [PontoEmbarqueController::class, 'vincular'])->middleware('permission:geocercas.write');
    Route::match(['put', 'patch'], 'pontos-embarque/linhas/{vinculo}', [PontoEmbarqueController::class, 'atualizarVinculo'])->middleware('permission:geocercas.write');
    Route::delete('pontos-embarque/linhas/{vinculo}', [PontoEmbarqueController::class, 'desvincular'])->middleware('permission:geocercas.write');
    Route::apiResource('pontos-embarque', PontoEmbarqueController::class)
        ->only(['index', 'show'])->parameters(['pontos-embarque' => 'ponto'])->middleware('permission:geocercas.view');
    Route::apiResource('pontos-embarque', PontoEmbarqueController::class)
        ->only(['store', 'update', 'destroy'])->parameters(['pontos-embarque' => 'ponto'])->middleware('permission:geocercas.write');

    // Atendimento: aprovar/rejeitar vinculacao e troca, revogar credenciais.
    Route::get('dispositivos/solicitacoes', [AnaliseDispositivoController::class, 'solicitacoes'])->middleware('permission:dispositivos.view');
    Route::get('dispositivos/solicitacoes/{solicitacao}', [AnaliseDispositivoController::class, 'solicitacao'])->middleware('permission:dispositivos.view');
    Route::patch('dispositivos/solicitacoes/{solicitacao}/aprovar', [AnaliseDispositivoController::class, 'aprovar'])->middleware('permission:dispositivos.analise');
    Route::patch('dispositivos/solicitacoes/{solicitacao}/rejeitar', [AnaliseDispositivoController::class, 'rejeitar'])->middleware('permission:dispositivos.analise');
    Route::get('dispositivos', [AnaliseDispositivoController::class, 'dispositivos'])->middleware('permission:dispositivos.view');
    Route::patch('dispositivos/{dispositivo}/revogar', [AnaliseDispositivoController::class, 'revogar'])->middleware('permission:dispositivos.analise');

    // Acompanhamento das presencas, revisoes, excecoes e auditoria.
    Route::get('presencas', [PresencaController::class, 'index'])->middleware('permission:presencas.view');
    Route::post('presencas/manual', [PresencaController::class, 'manual'])->middleware('permission:presencas.analise');
    Route::get('presencas/tentativas', [PresencaController::class, 'tentativas'])->middleware('permission:presencas.view');
    Route::put('presencas/tentativas/{tentativa}/revisao', [PresencaController::class, 'analisarRevisao'])->middleware('permission:presencas.analise');
    Route::get('presencas/indicadores', [PresencaController::class, 'indicadores'])->middleware('permission:presencas.view');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->middleware('permission:auditoria.view');
});
