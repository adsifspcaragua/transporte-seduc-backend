<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>API Transporte SEDUC</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://127.0.0.1:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.9.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.9.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Pesquisar">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introducao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introducao">
                    <a href="#introducao">Introducao</a>
                </li>
                            </ul>
                    <ul id="tocify-header-autenticacao-das-requisicoes" class="tocify-header">
                <li class="tocify-item level-1" data-unique="autenticacao-das-requisicoes">
                    <a href="#autenticacao-das-requisicoes">Autenticacao das requisicoes</a>
                </li>
                            </ul>
                    <ul id="tocify-header-autenticacao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="autenticacao">
                    <a href="#autenticacao">Autenticacao</a>
                </li>
                                    <ul id="tocify-subheader-autenticacao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-login">
                                <a href="#autenticacao-POSTapi-login">Login com sessao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-auth-token">
                                <a href="#autenticacao-POSTapi-auth-token">Gerar token de acesso.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-GETapi-me">
                                <a href="#autenticacao-GETapi-me">Exibir usuario autenticado.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-logout">
                                <a href="#autenticacao-POSTapi-logout">Encerrar sessao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-auth-token-revoke">
                                <a href="#autenticacao-POSTapi-auth-token-revoke">Revogar token atual.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-usuarios" class="tocify-header">
                <li class="tocify-item level-1" data-unique="usuarios">
                    <a href="#usuarios">Usuarios</a>
                </li>
                                    <ul id="tocify-subheader-usuarios" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="usuarios-GETapi-users">
                                <a href="#usuarios-GETapi-users">Listar usuarios.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="usuarios-POSTapi-users">
                                <a href="#usuarios-POSTapi-users">Cadastrar usuario.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="usuarios-GETapi-users--id-">
                                <a href="#usuarios-GETapi-users--id-">Exibir usuario.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="usuarios-PUTapi-users--id-">
                                <a href="#usuarios-PUTapi-users--id-">Atualizar usuario.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="usuarios-DELETEapi-users--id-">
                                <a href="#usuarios-DELETEapi-users--id-">Remover usuario.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-cargos" class="tocify-header">
                <li class="tocify-item level-1" data-unique="cargos">
                    <a href="#cargos">Cargos</a>
                </li>
                                    <ul id="tocify-subheader-cargos" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="cargos-GETapi-roles">
                                <a href="#cargos-GETapi-roles">Listar cargos.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cargos-POSTapi-roles">
                                <a href="#cargos-POSTapi-roles">Cadastrar cargo.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cargos-GETapi-roles--id-">
                                <a href="#cargos-GETapi-roles--id-">Exibir cargo.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cargos-PUTapi-roles--id-">
                                <a href="#cargos-PUTapi-roles--id-">Atualizar cargo.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cargos-DELETEapi-roles--id-">
                                <a href="#cargos-DELETEapi-roles--id-">Remover cargo.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-estudantes" class="tocify-header">
                <li class="tocify-item level-1" data-unique="estudantes">
                    <a href="#estudantes">Estudantes</a>
                </li>
                                    <ul id="tocify-subheader-estudantes" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="estudantes-GETapi-estudantes">
                                <a href="#estudantes-GETapi-estudantes">Listar estudantes.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="estudantes-POSTapi-estudantes">
                                <a href="#estudantes-POSTapi-estudantes">Cadastrar estudante.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="estudantes-GETapi-estudantes--id-">
                                <a href="#estudantes-GETapi-estudantes--id-">Exibir estudante.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="estudantes-PUTapi-estudantes--id-">
                                <a href="#estudantes-PUTapi-estudantes--id-">Atualizar estudante.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="estudantes-DELETEapi-estudantes--id-">
                                <a href="#estudantes-DELETEapi-estudantes--id-">Remover estudante.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="estudantes-GETapi-contar-estudantes">
                                <a href="#estudantes-GETapi-contar-estudantes">Contar estudantes.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-inscricoes" class="tocify-header">
                <li class="tocify-item level-1" data-unique="inscricoes">
                    <a href="#inscricoes">Inscricoes</a>
                </li>
                                    <ul id="tocify-subheader-inscricoes" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="inscricoes-POSTapi-inscricoes-recadastro">
                                <a href="#inscricoes-POSTapi-inscricoes-recadastro">Ativar recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="inscricoes-GETapi-inscricoes">
                                <a href="#inscricoes-GETapi-inscricoes">Listar inscricoes.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="inscricoes-POSTapi-inscricoes">
                                <a href="#inscricoes-POSTapi-inscricoes">Criar inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="inscricoes-GETapi-inscricoes--inscricao-">
                                <a href="#inscricoes-GETapi-inscricoes--inscricao-">Exibir inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="inscricoes-PUTapi-inscricoes--inscricao-">
                                <a href="#inscricoes-PUTapi-inscricoes--inscricao-">Atualizar inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="inscricoes-DELETEapi-inscricoes--inscricao-">
                                <a href="#inscricoes-DELETEapi-inscricoes--inscricao-">Remover inscricao.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-documentos-da-inscricao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="documentos-da-inscricao">
                    <a href="#documentos-da-inscricao">Documentos da inscricao</a>
                </li>
                                    <ul id="tocify-subheader-documentos-da-inscricao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="documentos-da-inscricao-GETapi-inscricoes--inscricao_id--documentos">
                                <a href="#documentos-da-inscricao-GETapi-inscricoes--inscricao_id--documentos">Listar documentos da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="documentos-da-inscricao-POSTapi-inscricoes--inscricao_id--documentos">
                                <a href="#documentos-da-inscricao-POSTapi-inscricoes--inscricao_id--documentos">Enviar documento da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="documentos-da-inscricao-GETapi-inscricoes--inscricao_id--documentos--id-">
                                <a href="#documentos-da-inscricao-GETapi-inscricoes--inscricao_id--documentos--id-">Exibir documento da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="documentos-da-inscricao-PUTapi-inscricoes--inscricao_id--documentos--id-">
                                <a href="#documentos-da-inscricao-PUTapi-inscricoes--inscricao_id--documentos--id-">Atualizar documento da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="documentos-da-inscricao-DELETEapi-inscricoes--inscricao_id--documentos--id-">
                                <a href="#documentos-da-inscricao-DELETEapi-inscricoes--inscricao_id--documentos--id-">Remover documento da inscricao.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-instituicoes-da-inscricao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="instituicoes-da-inscricao">
                    <a href="#instituicoes-da-inscricao">Instituicoes da inscricao</a>
                </li>
                                    <ul id="tocify-subheader-instituicoes-da-inscricao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="instituicoes-da-inscricao-GETapi-inscricoes--inscricao_id--instituicoes">
                                <a href="#instituicoes-da-inscricao-GETapi-inscricoes--inscricao_id--instituicoes">Listar instituicoes da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-da-inscricao-POSTapi-inscricoes--inscricao_id--instituicoes">
                                <a href="#instituicoes-da-inscricao-POSTapi-inscricoes--inscricao_id--instituicoes">Cadastrar instituicao na inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-da-inscricao-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-">
                                <a href="#instituicoes-da-inscricao-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-">Exibir instituicao da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-da-inscricao-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-">
                                <a href="#instituicoes-da-inscricao-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-">Atualizar instituicao da inscricao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-da-inscricao-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-">
                                <a href="#instituicoes-da-inscricao-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-">Remover instituicao da inscricao.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-instituicoes" class="tocify-header">
                <li class="tocify-item level-1" data-unique="instituicoes">
                    <a href="#instituicoes">Instituicoes</a>
                </li>
                                    <ul id="tocify-subheader-instituicoes" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="instituicoes-GETapi-instituicao">
                                <a href="#instituicoes-GETapi-instituicao">Listar instituicoes.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-POSTapi-instituicao">
                                <a href="#instituicoes-POSTapi-instituicao">Cadastrar instituicao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-GETapi-instituicao--id-">
                                <a href="#instituicoes-GETapi-instituicao--id-">Exibir instituicao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-PUTapi-instituicao--id-">
                                <a href="#instituicoes-PUTapi-instituicao--id-">Atualizar instituicao.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="instituicoes-DELETEapi-instituicao--id-">
                                <a href="#instituicoes-DELETEapi-instituicao--id-">Remover instituicao.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-linhas" class="tocify-header">
                <li class="tocify-item level-1" data-unique="linhas">
                    <a href="#linhas">Linhas</a>
                </li>
                                    <ul id="tocify-subheader-linhas" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="linhas-GETapi-linha">
                                <a href="#linhas-GETapi-linha">Listar linhas.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="linhas-POSTapi-linha">
                                <a href="#linhas-POSTapi-linha">Cadastrar linha.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="linhas-GETapi-linha--id-">
                                <a href="#linhas-GETapi-linha--id-">Exibir linha.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="linhas-PUTapi-linha--id-">
                                <a href="#linhas-PUTapi-linha--id-">Atualizar linha.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="linhas-DELETEapi-linha--id-">
                                <a href="#linhas-DELETEapi-linha--id-">Remover linha.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-recadastro" class="tocify-header">
                <li class="tocify-item level-1" data-unique="recadastro">
                    <a href="#recadastro">Recadastro</a>
                </li>
                                    <ul id="tocify-subheader-recadastro" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="recadastro-GETapi-estudantes-reecadastrar">
                                <a href="#recadastro-GETapi-estudantes-reecadastrar">Listar documentos de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-POSTapi-estudantes-reecadastrar">
                                <a href="#recadastro-POSTapi-estudantes-reecadastrar">Cadastrar documento de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-GETapi-estudantes-reecadastrar--documento-">
                                <a href="#recadastro-GETapi-estudantes-reecadastrar--documento-">Exibir documento de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-PUTapi-estudantes-reecadastrar--documento-">
                                <a href="#recadastro-PUTapi-estudantes-reecadastrar--documento-">Atualizar documento de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-DELETEapi-estudantes-reecadastrar--documento-">
                                <a href="#recadastro-DELETEapi-estudantes-reecadastrar--documento-">Remover documento de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-GETapi-reecadastro-solicitacoes">
                                <a href="#recadastro-GETapi-reecadastro-solicitacoes">Listar solicitacoes de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-POSTapi-reecadastro-solicitacoes">
                                <a href="#recadastro-POSTapi-reecadastro-solicitacoes">Cadastrar solicitacao de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-GETapi-reecadastro-solicitacoes--solicitacao-">
                                <a href="#recadastro-GETapi-reecadastro-solicitacoes--solicitacao-">Exibir solicitacao de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-PUTapi-reecadastro-solicitacoes--solicitacao-">
                                <a href="#recadastro-PUTapi-reecadastro-solicitacoes--solicitacao-">Atualizar solicitacao de recadastro.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="recadastro-DELETEapi-reecadastro-solicitacoes--solicitacao-">
                                <a href="#recadastro-DELETEapi-reecadastro-solicitacoes--solicitacao-">Remover solicitacao de recadastro.</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">Ver colecao Postman</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">Ver especificacao OpenAPI</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Atualizado em: 07/05/2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introducao">Introducao</h1>
<p>Documentacao da API do sistema de transporte escolar da SEDUC.</p>
<aside>
    <strong>URL base</strong>: <code>http://127.0.0.1:8000</code>
</aside>
<p>Esta documentacao reune as rotas disponiveis para autenticacao, inscricoes, estudantes, instituicoes, linhas, usuarios, cargos e recadastro.</p>
<aside>Para usar as rotas protegidas, autentique-se em <code>POST /api/auth/token</code> e envie o token recebido no cabecalho <code>Authorization: Bearer TOKEN</code>.</aside>

        <h1 id="autenticacao-das-requisicoes">Autenticacao das requisicoes</h1>
<p>Para autenticar as requisicoes, inclua o cabecalho <strong><code>Authorization</code></strong> com o valor <strong><code>"Bearer TOKEN_DE_ACESSO"</code></strong>.</p>
<p>Todos os endpoints autenticados aparecem marcados com o selo <code>requer autenticacao</code> na documentacao abaixo.</p>
<p>Use <code>POST /api/auth/token</code> informando e-mail ou CPF e senha para gerar um token Bearer.</p>

        <h1 id="autenticacao">Autenticacao</h1>

    <p>Rotas para login por sessao, emissao de token Bearer e encerramento de acesso.</p>

                                <h2 id="autenticacao-POSTapi-login">Login com sessao.</h2>

<p>
</p>

<p>Autentica um usuario usando e-mail ou CPF e cria uma sessao web.</p>

<span id="example-requests-POSTapi-login">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"login\": \"usuario@example.com\",
    \"password\": \"password\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "login": "usuario@example.com",
    "password": "password"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-login">
</span>
<span id="execution-results-POSTapi-login" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-login"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-login" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-login">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-login" data-method="POST"
      data-path="api/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-login', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-login"
                    onclick="tryItOut('POSTapi-login');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-login"
                    onclick="cancelTryOut('POSTapi-login');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-login"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>login</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="login"                data-endpoint="POSTapi-login"
               value="usuario@example.com"
               data-component="body">
    <br>
<p>E-mail ou CPF do usuario. Example: <code>usuario@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-login"
               value="password"
               data-component="body">
    <br>
<p>Senha do usuario. Example: <code>password</code></p>
        </div>
        </form>

                    <h2 id="autenticacao-POSTapi-auth-token">Gerar token de acesso.</h2>

<p>
</p>

<p>Autentica um usuario usando e-mail ou CPF e retorna um token Bearer para uso nas rotas protegidas.</p>

<span id="example-requests-POSTapi-auth-token">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/auth/token" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"login\": \"usuario@example.com\",
    \"password\": \"password\",
    \"device_name\": \"insomnia\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/auth/token"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "login": "usuario@example.com",
    "password": "password",
    "device_name": "insomnia"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-auth-token">
</span>
<span id="execution-results-POSTapi-auth-token" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-auth-token"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-auth-token"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-auth-token" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-auth-token">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-auth-token" data-method="POST"
      data-path="api/auth/token"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-auth-token', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-auth-token"
                    onclick="tryItOut('POSTapi-auth-token');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-auth-token"
                    onclick="cancelTryOut('POSTapi-auth-token');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-auth-token"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/auth/token</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-auth-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-auth-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>login</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="login"                data-endpoint="POSTapi-auth-token"
               value="usuario@example.com"
               data-component="body">
    <br>
<p>E-mail ou CPF do usuario. Example: <code>usuario@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-auth-token"
               value="password"
               data-component="body">
    <br>
<p>Senha do usuario. Example: <code>password</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-auth-token"
               value="insomnia"
               data-component="body">
    <br>
<p>Nome do dispositivo usado para identificar o token. Example: <code>insomnia</code></p>
        </div>
        </form>

                    <h2 id="autenticacao-GETapi-me">Exibir usuario autenticado.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados do usuario autenticado na requisicao atual.</p>

<span id="example-requests-GETapi-me">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/me" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/me"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-me">
    </span>
<span id="execution-results-GETapi-me" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-me"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-me" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-me">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-me" data-method="GET"
      data-path="api/me"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-me', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-me"
                    onclick="tryItOut('GETapi-me');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-me"
                    onclick="cancelTryOut('GETapi-me');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-me"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-me"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="autenticacao-POSTapi-logout">Encerrar sessao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Encerra a sessao web autenticada.</p>

<span id="example-requests-POSTapi-logout">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/logout" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/logout"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-logout">
</span>
<span id="execution-results-POSTapi-logout" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-logout"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-logout" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-logout">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-logout" data-method="POST"
      data-path="api/logout"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-logout', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-logout"
                    onclick="tryItOut('POSTapi-logout');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-logout"
                    onclick="cancelTryOut('POSTapi-logout');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-logout"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-logout"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="autenticacao-POSTapi-auth-token-revoke">Revogar token atual.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Revoga o token Bearer enviado na requisicao atual.</p>

<span id="example-requests-POSTapi-auth-token-revoke">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/auth/token/revoke" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/auth/token/revoke"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-auth-token-revoke">
</span>
<span id="execution-results-POSTapi-auth-token-revoke" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-auth-token-revoke"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-auth-token-revoke"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-auth-token-revoke" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-auth-token-revoke">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-auth-token-revoke" data-method="POST"
      data-path="api/auth/token/revoke"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-auth-token-revoke', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-auth-token-revoke"
                    onclick="tryItOut('POSTapi-auth-token-revoke');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-auth-token-revoke"
                    onclick="cancelTryOut('POSTapi-auth-token-revoke');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-auth-token-revoke"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/auth/token/revoke</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-auth-token-revoke"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-auth-token-revoke"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-auth-token-revoke"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="usuarios">Usuarios</h1>

    <p>Rotas para gerenciamento de usuarios do sistema.</p>

                                <h2 id="usuarios-GETapi-users">Listar usuarios.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna todos os usuarios cadastrados.</p>

<span id="example-requests-GETapi-users">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/users" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/users"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-users">
    </span>
<span id="execution-results-GETapi-users" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-users"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-users" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-users">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-users" data-method="GET"
      data-path="api/users"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-users', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-users"
                    onclick="tryItOut('GETapi-users');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-users"
                    onclick="cancelTryOut('GETapi-users');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-users"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-users"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="usuarios-POSTapi-users">Cadastrar usuario.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria um novo usuario do sistema.</p>

<span id="example-requests-POSTapi-users">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/users" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Maria Silva\",
    \"email\": \"maria@example.com\",
    \"password\": \"password123\",
    \"cpf\": \"12345678901\",
    \"matricula\": 12345,
    \"data_nascimento\": \"1990-05-10\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/users"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Maria Silva",
    "email": "maria@example.com",
    "password": "password123",
    "cpf": "12345678901",
    "matricula": 12345,
    "data_nascimento": "1990-05-10"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-users">
</span>
<span id="execution-results-POSTapi-users" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-users"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-users"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-users" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-users">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-users" data-method="POST"
      data-path="api/users"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-users', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-users"
                    onclick="tryItOut('POSTapi-users');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-users"
                    onclick="cancelTryOut('POSTapi-users');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-users"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/users</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-users"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-users"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-users"
               value="Maria Silva"
               data-component="body">
    <br>
<p>Nome completo do usuario. Must not be greater than 255 characters. Example: <code>Maria Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-users"
               value="maria@example.com"
               data-component="body">
    <br>
<p>E-mail unico do usuario. Must be a valid email address. Example: <code>maria@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-users"
               value="password123"
               data-component="body">
    <br>
<p>Senha com no minimo 8 caracteres. Must be at least 8 characters. Example: <code>password123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cpf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cpf"                data-endpoint="POSTapi-users"
               value="12345678901"
               data-component="body">
    <br>
<p>CPF do usuario com 11 digitos. Must be at least 11 characters. Must not be greater than 11 characters. Example: <code>12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>matricula</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="matricula"                data-endpoint="POSTapi-users"
               value="12345"
               data-component="body">
    <br>
<p>Numero de matricula do usuario. Example: <code>12345</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>data_nascimento</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="data_nascimento"                data-endpoint="POSTapi-users"
               value="1990-05-10"
               data-component="body">
    <br>
<p>Data de nascimento no formato AAAA-MM-DD. Must be a valid date. Must be a valid date in the format <code>Y-m-d</code>. Must be a date before <code>today</code>. Example: <code>1990-05-10</code></p>
        </div>
        </form>

                    <h2 id="usuarios-GETapi-users--id-">Exibir usuario.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados de um usuario especifico.</p>

<span id="example-requests-GETapi-users--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/users/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/users/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-users--id-">
    </span>
<span id="execution-results-GETapi-users--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-users--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-users--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-users--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-users--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-users--id-" data-method="GET"
      data-path="api/users/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-users--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-users--id-"
                    onclick="tryItOut('GETapi-users--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-users--id-"
                    onclick="cancelTryOut('GETapi-users--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-users--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/users/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-users--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-users--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-users--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-users--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user"                data-endpoint="GETapi-users--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do usuario. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="usuarios-PUTapi-users--id-">Atualizar usuario.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de um usuario existente.</p>

<span id="example-requests-PUTapi-users--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/users/16" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"maria@example.com\",
    \"name\": \"Maria Silva\",
    \"password\": \"password123\",
    \"cpf\": \"12345678901\",
    \"matricula\": 12345,
    \"data_nascimento\": \"1990-05-10\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/users/16"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "maria@example.com",
    "name": "Maria Silva",
    "password": "password123",
    "cpf": "12345678901",
    "matricula": 12345,
    "data_nascimento": "1990-05-10"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-users--id-">
</span>
<span id="execution-results-PUTapi-users--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-users--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-users--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-users--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-users--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-users--id-" data-method="PUT"
      data-path="api/users/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-users--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-users--id-"
                    onclick="tryItOut('PUTapi-users--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-users--id-"
                    onclick="cancelTryOut('PUTapi-users--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-users--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/users/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/users/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-users--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-users--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-users--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="PUTapi-users--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user"                data-endpoint="PUTapi-users--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do usuario. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="PUTapi-users--id-"
               value="maria@example.com"
               data-component="body">
    <br>
<p>E-mail unico do usuario. Must be a valid email address. Example: <code>maria@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-users--id-"
               value="Maria Silva"
               data-component="body">
    <br>
<p>Nome completo do usuario. Must not be greater than 255 characters. Example: <code>Maria Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="PUTapi-users--id-"
               value="password123"
               data-component="body">
    <br>
<p>Senha com no minimo 8 caracteres. Must be at least 8 characters. Example: <code>password123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cpf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cpf"                data-endpoint="PUTapi-users--id-"
               value="12345678901"
               data-component="body">
    <br>
<p>CPF do usuario com 11 digitos. Must be at least 11 characters. Must not be greater than 11 characters. Example: <code>12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>matricula</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="matricula"                data-endpoint="PUTapi-users--id-"
               value="12345"
               data-component="body">
    <br>
<p>Numero de matricula do usuario. Example: <code>12345</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>data_nascimento</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="data_nascimento"                data-endpoint="PUTapi-users--id-"
               value="1990-05-10"
               data-component="body">
    <br>
<p>Data de nascimento no formato AAAA-MM-DD. Must be a valid date. Must be a valid date in the format <code>Y-m-d</code>. Must be a date before <code>today</code>. Example: <code>1990-05-10</code></p>
        </div>
        </form>

                    <h2 id="usuarios-DELETEapi-users--id-">Remover usuario.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove um usuario cadastrado.</p>

<span id="example-requests-DELETEapi-users--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/users/16" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/users/16"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-users--id-">
</span>
<span id="execution-results-DELETEapi-users--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-users--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-users--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-users--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-users--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-users--id-" data-method="DELETE"
      data-path="api/users/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-users--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-users--id-"
                    onclick="tryItOut('DELETEapi-users--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-users--id-"
                    onclick="cancelTryOut('DELETEapi-users--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-users--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/users/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-users--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-users--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-users--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-users--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the user. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>user</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user"                data-endpoint="DELETEapi-users--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do usuario. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="cargos">Cargos</h1>

    <p>Rotas para gerenciamento dos cargos de usuarios.</p>

                                <h2 id="cargos-GETapi-roles">Listar cargos.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna todos os cargos cadastrados.</p>

<span id="example-requests-GETapi-roles">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/roles" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/roles"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-roles">
    </span>
<span id="execution-results-GETapi-roles" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-roles"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-roles"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-roles" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-roles">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-roles" data-method="GET"
      data-path="api/roles"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-roles', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-roles"
                    onclick="tryItOut('GETapi-roles');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-roles"
                    onclick="cancelTryOut('GETapi-roles');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-roles"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/roles</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-roles"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-roles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-roles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="cargos-POSTapi-roles">Cadastrar cargo.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria um novo cargo.</p>

<span id="example-requests-POSTapi-roles">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/roles" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"title\": \"Administrador\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/roles"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "Administrador"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-roles">
</span>
<span id="execution-results-POSTapi-roles" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-roles"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-roles"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-roles" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-roles">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-roles" data-method="POST"
      data-path="api/roles"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-roles', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-roles"
                    onclick="tryItOut('POSTapi-roles');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-roles"
                    onclick="cancelTryOut('POSTapi-roles');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-roles"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/roles</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-roles"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-roles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-roles"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-roles"
               value="Administrador"
               data-component="body">
    <br>
<p>Nome do cargo. Must be at least 1 character. Must not be greater than 100 characters. Example: <code>Administrador</code></p>
        </div>
        </form>

                    <h2 id="cargos-GETapi-roles--id-">Exibir cargo.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados de um cargo especifico.</p>

<span id="example-requests-GETapi-roles--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/roles/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/roles/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-roles--id-">
    </span>
<span id="execution-results-GETapi-roles--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-roles--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-roles--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-roles--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-roles--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-roles--id-" data-method="GET"
      data-path="api/roles/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-roles--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-roles--id-"
                    onclick="tryItOut('GETapi-roles--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-roles--id-"
                    onclick="cancelTryOut('GETapi-roles--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-roles--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/roles/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-roles--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-roles--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-roles--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-roles--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the role. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="role"                data-endpoint="GETapi-roles--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do cargo. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="cargos-PUTapi-roles--id-">Atualizar cargo.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de um cargo existente.</p>

<span id="example-requests-PUTapi-roles--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/roles/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"title\": \"Administrador\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/roles/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "Administrador"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-roles--id-">
</span>
<span id="execution-results-PUTapi-roles--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-roles--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-roles--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-roles--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-roles--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-roles--id-" data-method="PUT"
      data-path="api/roles/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-roles--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-roles--id-"
                    onclick="tryItOut('PUTapi-roles--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-roles--id-"
                    onclick="cancelTryOut('PUTapi-roles--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-roles--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/roles/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/roles/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-roles--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-roles--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-roles--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PUTapi-roles--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the role. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="role"                data-endpoint="PUTapi-roles--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do cargo. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="PUTapi-roles--id-"
               value="Administrador"
               data-component="body">
    <br>
<p>Nome do cargo. Must be at least 1 character. Must not be greater than 100 characters. Example: <code>Administrador</code></p>
        </div>
        </form>

                    <h2 id="cargos-DELETEapi-roles--id-">Remover cargo.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove um cargo cadastrado.</p>

<span id="example-requests-DELETEapi-roles--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/roles/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/roles/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-roles--id-">
</span>
<span id="execution-results-DELETEapi-roles--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-roles--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-roles--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-roles--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-roles--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-roles--id-" data-method="DELETE"
      data-path="api/roles/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-roles--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-roles--id-"
                    onclick="tryItOut('DELETEapi-roles--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-roles--id-"
                    onclick="cancelTryOut('DELETEapi-roles--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-roles--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/roles/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-roles--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-roles--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-roles--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-roles--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the role. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="role"                data-endpoint="DELETEapi-roles--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do cargo. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="estudantes">Estudantes</h1>

    <p>Rotas para cadastrar, consultar, atualizar, remover e contar estudantes.</p>

                                <h2 id="estudantes-GETapi-estudantes">Listar estudantes.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna a lista paginada de estudantes cadastrados.</p>

<span id="example-requests-GETapi-estudantes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/estudantes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-estudantes">
    </span>
<span id="execution-results-GETapi-estudantes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-estudantes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-estudantes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-estudantes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-estudantes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-estudantes" data-method="GET"
      data-path="api/estudantes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-estudantes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-estudantes"
                    onclick="tryItOut('GETapi-estudantes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-estudantes"
                    onclick="cancelTryOut('GETapi-estudantes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-estudantes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/estudantes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-estudantes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-estudantes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-estudantes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="estudantes-POSTapi-estudantes">Cadastrar estudante.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria um estudante e define o status inicial como "Em espera".</p>

<span id="example-requests-POSTapi-estudantes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/estudantes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Joao da Silva\",
    \"email\": \"joao@example.com\",
    \"cpf\": \"12345678901\",
    \"birth_date\": \"2005-08-15\",
    \"phone\": \"77999999999\",
    \"address\": \"Rua Principal, 100\",
    \"start_time\": \"07:30\",
    \"end_time\": \"12:00\",
    \"days_of_week\": [
        \"segunda\"
    ],
    \"observation\": \"Necessita embarque no ponto central.\",
    \"linha_id\": 1,
    \"user_id\": 1,
    \"inscricao_id\": 1,
    \"instituicao_id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Joao da Silva",
    "email": "joao@example.com",
    "cpf": "12345678901",
    "birth_date": "2005-08-15",
    "phone": "77999999999",
    "address": "Rua Principal, 100",
    "start_time": "07:30",
    "end_time": "12:00",
    "days_of_week": [
        "segunda"
    ],
    "observation": "Necessita embarque no ponto central.",
    "linha_id": 1,
    "user_id": 1,
    "inscricao_id": 1,
    "instituicao_id": 1
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-estudantes">
</span>
<span id="execution-results-POSTapi-estudantes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-estudantes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-estudantes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-estudantes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-estudantes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-estudantes" data-method="POST"
      data-path="api/estudantes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-estudantes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-estudantes"
                    onclick="tryItOut('POSTapi-estudantes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-estudantes"
                    onclick="cancelTryOut('POSTapi-estudantes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-estudantes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/estudantes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-estudantes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-estudantes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-estudantes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-estudantes"
               value="Joao da Silva"
               data-component="body">
    <br>
<p>Nome completo do estudante. Must not be greater than 255 characters. Example: <code>Joao da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-estudantes"
               value="joao@example.com"
               data-component="body">
    <br>
<p>E-mail unico do estudante. Must be a valid email address. Example: <code>joao@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cpf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cpf"                data-endpoint="POSTapi-estudantes"
               value="12345678901"
               data-component="body">
    <br>
<p>CPF do estudante com 11 digitos. Must be 11 characters. Example: <code>12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birth_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birth_date"                data-endpoint="POSTapi-estudantes"
               value="2005-08-15"
               data-component="body">
    <br>
<p>Data de nascimento do estudante. Must be a valid date. Must be a date before <code>today</code>. Example: <code>2005-08-15</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-estudantes"
               value="77999999999"
               data-component="body">
    <br>
<p>Telefone para contato. Must not be greater than 15 characters. Example: <code>77999999999</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="POSTapi-estudantes"
               value="Rua Principal, 100"
               data-component="body">
    <br>
<p>Endereco do estudante. Must not be greater than 255 characters. Example: <code>Rua Principal, 100</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>start_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="start_time"                data-endpoint="POSTapi-estudantes"
               value="07:30"
               data-component="body">
    <br>
<p>Horario de inicio das aulas no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Example: <code>07:30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>end_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="end_time"                data-endpoint="POSTapi-estudantes"
               value="12:00"
               data-component="body">
    <br>
<p>Horario de termino das aulas no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Must be a date after <code>start_time</code>. Example: <code>12:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>days_of_week</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="days_of_week[0]"                data-endpoint="POSTapi-estudantes"
               data-component="body">
        <input type="text" style="display: none"
               name="days_of_week[1]"                data-endpoint="POSTapi-estudantes"
               data-component="body">
    <br>
<p>Dia da semana.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>observation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="observation"                data-endpoint="POSTapi-estudantes"
               value="Necessita embarque no ponto central."
               data-component="body">
    <br>
<p>Observacao opcional sobre o estudante. Must not be greater than 1000 characters. Example: <code>Necessita embarque no ponto central.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-estudantes"
               value=""
               data-component="body">
    <br>
<p>Campo controlado pelo sistema. Nao envie este campo.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>linha_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linha_id"                data-endpoint="POSTapi-estudantes"
               value="1"
               data-component="body">
    <br>
<p>ID da linha vinculada ao estudante. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="POSTapi-estudantes"
               value="1"
               data-component="body">
    <br>
<p>ID do usuario associado ao estudante. The <code>id</code> of an existing record in the users table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="POSTapi-estudantes"
               value="1"
               data-component="body">
    <br>
<p>ID da inscricao que originou o estudante. The <code>id</code> of an existing record in the inscricoes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>instituicao_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="instituicao_id"                data-endpoint="POSTapi-estudantes"
               value="1"
               data-component="body">
    <br>
<p>ID da instituicao do estudante. The <code>id</code> of an existing record in the instituicoes table. Example: <code>1</code></p>
        </div>
        </form>

                    <h2 id="estudantes-GETapi-estudantes--id-">Exibir estudante.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados de um estudante especifico.</p>

<span id="example-requests-GETapi-estudantes--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/estudantes/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-estudantes--id-">
    </span>
<span id="execution-results-GETapi-estudantes--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-estudantes--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-estudantes--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-estudantes--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-estudantes--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-estudantes--id-" data-method="GET"
      data-path="api/estudantes/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-estudantes--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-estudantes--id-"
                    onclick="tryItOut('GETapi-estudantes--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-estudantes--id-"
                    onclick="cancelTryOut('GETapi-estudantes--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-estudantes--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/estudantes/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-estudantes--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-estudantes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-estudantes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-estudantes--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the estudante. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>estudante</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="estudante"                data-endpoint="GETapi-estudantes--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do estudante. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="estudantes-PUTapi-estudantes--id-">Atualizar estudante.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de um estudante existente.</p>

<span id="example-requests-PUTapi-estudantes--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/estudantes/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Joao da Silva\",
    \"email\": \"joao@example.com\",
    \"cpf\": \"12345678901\",
    \"birth_date\": \"2005-08-15\",
    \"phone\": \"77999999999\",
    \"address\": \"Rua Principal, 100\",
    \"start_time\": \"07:30\",
    \"end_time\": \"12:00\",
    \"days_of_week\": [
        \"segunda\"
    ],
    \"observation\": \"Necessita embarque no ponto central.\",
    \"status\": \"ATIVO\",
    \"linha_id\": 1,
    \"user_id\": 1,
    \"instituicao_id\": 1,
    \"inscricao_id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Joao da Silva",
    "email": "joao@example.com",
    "cpf": "12345678901",
    "birth_date": "2005-08-15",
    "phone": "77999999999",
    "address": "Rua Principal, 100",
    "start_time": "07:30",
    "end_time": "12:00",
    "days_of_week": [
        "segunda"
    ],
    "observation": "Necessita embarque no ponto central.",
    "status": "ATIVO",
    "linha_id": 1,
    "user_id": 1,
    "instituicao_id": 1,
    "inscricao_id": 1
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-estudantes--id-">
</span>
<span id="execution-results-PUTapi-estudantes--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-estudantes--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-estudantes--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-estudantes--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-estudantes--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-estudantes--id-" data-method="PUT"
      data-path="api/estudantes/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-estudantes--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-estudantes--id-"
                    onclick="tryItOut('PUTapi-estudantes--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-estudantes--id-"
                    onclick="cancelTryOut('PUTapi-estudantes--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-estudantes--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/estudantes/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/estudantes/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-estudantes--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-estudantes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-estudantes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PUTapi-estudantes--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the estudante. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>estudante</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="estudante"                data-endpoint="PUTapi-estudantes--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do estudante. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-estudantes--id-"
               value="Joao da Silva"
               data-component="body">
    <br>
<p>Nome completo do estudante. Must not be greater than 255 characters. Example: <code>Joao da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="PUTapi-estudantes--id-"
               value="joao@example.com"
               data-component="body">
    <br>
<p>E-mail unico do estudante. Must be a valid email address. Example: <code>joao@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cpf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cpf"                data-endpoint="PUTapi-estudantes--id-"
               value="12345678901"
               data-component="body">
    <br>
<p>CPF do estudante com 11 digitos. Must be 11 characters. Example: <code>12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birth_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birth_date"                data-endpoint="PUTapi-estudantes--id-"
               value="2005-08-15"
               data-component="body">
    <br>
<p>Data de nascimento do estudante. Must be a valid date. Must be a date before <code>today</code>. Example: <code>2005-08-15</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="PUTapi-estudantes--id-"
               value="77999999999"
               data-component="body">
    <br>
<p>Telefone para contato. Must not be greater than 15 characters. Example: <code>77999999999</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="PUTapi-estudantes--id-"
               value="Rua Principal, 100"
               data-component="body">
    <br>
<p>Endereco do estudante. Must not be greater than 255 characters. Example: <code>Rua Principal, 100</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>start_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="start_time"                data-endpoint="PUTapi-estudantes--id-"
               value="07:30"
               data-component="body">
    <br>
<p>Horario de inicio das aulas no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Example: <code>07:30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>end_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="end_time"                data-endpoint="PUTapi-estudantes--id-"
               value="12:00"
               data-component="body">
    <br>
<p>Horario de termino das aulas no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Must be a date after <code>start_time</code>. Example: <code>12:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>days_of_week</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="days_of_week[0]"                data-endpoint="PUTapi-estudantes--id-"
               data-component="body">
        <input type="text" style="display: none"
               name="days_of_week[1]"                data-endpoint="PUTapi-estudantes--id-"
               data-component="body">
    <br>
<p>Dia da semana.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>observation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="observation"                data-endpoint="PUTapi-estudantes--id-"
               value="Necessita embarque no ponto central."
               data-component="body">
    <br>
<p>Observacao opcional sobre o estudante. Must not be greater than 1000 characters. Example: <code>Necessita embarque no ponto central.</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-estudantes--id-"
               value="ATIVO"
               data-component="body">
    <br>
<p>Status atual do estudante. Must not be greater than 255 characters. Example: <code>ATIVO</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>linha_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linha_id"                data-endpoint="PUTapi-estudantes--id-"
               value="1"
               data-component="body">
    <br>
<p>ID da linha vinculada ao estudante. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>user_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="user_id"                data-endpoint="PUTapi-estudantes--id-"
               value="1"
               data-component="body">
    <br>
<p>ID do usuario associado ao estudante. The <code>id</code> of an existing record in the users table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>instituicao_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="instituicao_id"                data-endpoint="PUTapi-estudantes--id-"
               value="1"
               data-component="body">
    <br>
<p>ID da instituicao do estudante. The <code>id</code> of an existing record in the instituicoes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="PUTapi-estudantes--id-"
               value="1"
               data-component="body">
    <br>
<p>ID da inscricao que originou o estudante. The <code>id</code> of an existing record in the inscricoes table. Example: <code>1</code></p>
        </div>
        </form>

                    <h2 id="estudantes-DELETEapi-estudantes--id-">Remover estudante.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove um estudante do cadastro.</p>

<span id="example-requests-DELETEapi-estudantes--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/estudantes/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-estudantes--id-">
</span>
<span id="execution-results-DELETEapi-estudantes--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-estudantes--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-estudantes--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-estudantes--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-estudantes--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-estudantes--id-" data-method="DELETE"
      data-path="api/estudantes/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-estudantes--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-estudantes--id-"
                    onclick="tryItOut('DELETEapi-estudantes--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-estudantes--id-"
                    onclick="cancelTryOut('DELETEapi-estudantes--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-estudantes--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/estudantes/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-estudantes--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-estudantes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-estudantes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-estudantes--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the estudante. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>estudante</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="estudante"                data-endpoint="DELETEapi-estudantes--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do estudante. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="estudantes-GETapi-contar-estudantes">Contar estudantes.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna o total de estudantes cadastrados.</p>

<span id="example-requests-GETapi-contar-estudantes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/contar-estudantes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/contar-estudantes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-contar-estudantes">
    </span>
<span id="execution-results-GETapi-contar-estudantes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-contar-estudantes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-contar-estudantes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-contar-estudantes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-contar-estudantes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-contar-estudantes" data-method="GET"
      data-path="api/contar-estudantes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-contar-estudantes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-contar-estudantes"
                    onclick="tryItOut('GETapi-contar-estudantes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-contar-estudantes"
                    onclick="cancelTryOut('GETapi-contar-estudantes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-contar-estudantes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/contar-estudantes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-contar-estudantes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-contar-estudantes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-contar-estudantes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="inscricoes">Inscricoes</h1>

    <p>Rotas para criar, acompanhar e atualizar inscricoes de estudantes.</p>

                                <h2 id="inscricoes-POSTapi-inscricoes-recadastro">Ativar recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Redefine o status das inscricoes para incompleto.</p>

<span id="example-requests-POSTapi-inscricoes-recadastro">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/inscricoes/recadastro" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/recadastro"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-inscricoes-recadastro">
</span>
<span id="execution-results-POSTapi-inscricoes-recadastro" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-inscricoes-recadastro"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-inscricoes-recadastro"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-inscricoes-recadastro" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-inscricoes-recadastro">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-inscricoes-recadastro" data-method="POST"
      data-path="api/inscricoes/recadastro"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-inscricoes-recadastro', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-inscricoes-recadastro"
                    onclick="tryItOut('POSTapi-inscricoes-recadastro');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-inscricoes-recadastro"
                    onclick="cancelTryOut('POSTapi-inscricoes-recadastro');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-inscricoes-recadastro"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/inscricoes/recadastro</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-inscricoes-recadastro"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-inscricoes-recadastro"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-inscricoes-recadastro"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="inscricoes-GETapi-inscricoes">Listar inscricoes.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna todas as inscricoes cadastradas.</p>

<span id="example-requests-GETapi-inscricoes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/inscricoes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-inscricoes">
    </span>
<span id="execution-results-GETapi-inscricoes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-inscricoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-inscricoes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-inscricoes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-inscricoes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-inscricoes" data-method="GET"
      data-path="api/inscricoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-inscricoes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-inscricoes"
                    onclick="tryItOut('GETapi-inscricoes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-inscricoes"
                    onclick="cancelTryOut('GETapi-inscricoes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-inscricoes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/inscricoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-inscricoes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-inscricoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-inscricoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="inscricoes-POSTapi-inscricoes">Criar inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria uma nova inscricao e recalcula o status conforme os dados enviados.</p>

<span id="example-requests-POSTapi-inscricoes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/inscricoes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Joao da Silva\",
    \"cpf\": \"12345678901\",
    \"rg\": \"12345678\",
    \"father_name\": \"Jose da Silva\",
    \"mother_name\": \"Maria da Silva\",
    \"birth_date\": \"2005-08-15\",
    \"phone\": \"77999999999\",
    \"email\": \"joao@example.com\",
    \"cep\": \"45000000\",
    \"address\": \"Rua Principal\",
    \"neighborhood\": \"Centro\",
    \"complement\": \"Casa\",
    \"city\": \"Vitoria da Conquista\",
    \"number\": 100,
    \"accepted_terms\": true,
    \"accepted_terms_2\": true
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Joao da Silva",
    "cpf": "12345678901",
    "rg": "12345678",
    "father_name": "Jose da Silva",
    "mother_name": "Maria da Silva",
    "birth_date": "2005-08-15",
    "phone": "77999999999",
    "email": "joao@example.com",
    "cep": "45000000",
    "address": "Rua Principal",
    "neighborhood": "Centro",
    "complement": "Casa",
    "city": "Vitoria da Conquista",
    "number": 100,
    "accepted_terms": true,
    "accepted_terms_2": true
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-inscricoes">
</span>
<span id="execution-results-POSTapi-inscricoes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-inscricoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-inscricoes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-inscricoes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-inscricoes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-inscricoes" data-method="POST"
      data-path="api/inscricoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-inscricoes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-inscricoes"
                    onclick="tryItOut('POSTapi-inscricoes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-inscricoes"
                    onclick="cancelTryOut('POSTapi-inscricoes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-inscricoes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/inscricoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-inscricoes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-inscricoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-inscricoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-inscricoes"
               value="Joao da Silva"
               data-component="body">
    <br>
<p>Nome completo do estudante. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Joao da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cpf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cpf"                data-endpoint="POSTapi-inscricoes"
               value="12345678901"
               data-component="body">
    <br>
<p>CPF do estudante com 11 digitos. Must be 11 characters. Example: <code>12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>rg</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="rg"                data-endpoint="POSTapi-inscricoes"
               value="12345678"
               data-component="body">
    <br>
<p>RG do estudante. Must be at least 8 characters. Must not be greater than 11 characters. Example: <code>12345678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>father_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="father_name"                data-endpoint="POSTapi-inscricoes"
               value="Jose da Silva"
               data-component="body">
    <br>
<p>Nome do pai. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Jose da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mother_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mother_name"                data-endpoint="POSTapi-inscricoes"
               value="Maria da Silva"
               data-component="body">
    <br>
<p>Nome da mae. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Maria da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birth_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birth_date"                data-endpoint="POSTapi-inscricoes"
               value="2005-08-15"
               data-component="body">
    <br>
<p>Data de nascimento no formato AAAA-MM-DD. Must be a valid date. Must be a valid date in the format <code>Y-m-d</code>. Must be a date before <code>today</code>. Example: <code>2005-08-15</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="POSTapi-inscricoes"
               value="77999999999"
               data-component="body">
    <br>
<p>Telefone para contato. Must not be greater than 15 characters. Example: <code>77999999999</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-inscricoes"
               value="joao@example.com"
               data-component="body">
    <br>
<p>E-mail do estudante. Must be a valid email address. Example: <code>joao@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cep</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cep"                data-endpoint="POSTapi-inscricoes"
               value="45000000"
               data-component="body">
    <br>
<p>CEP com 8 digitos. Must be 8 characters. Example: <code>45000000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="POSTapi-inscricoes"
               value="Rua Principal"
               data-component="body">
    <br>
<p>Endereco residencial. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Rua Principal</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>neighborhood</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="neighborhood"                data-endpoint="POSTapi-inscricoes"
               value="Centro"
               data-component="body">
    <br>
<p>Bairro residencial. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Centro</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>complement</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="complement"                data-endpoint="POSTapi-inscricoes"
               value="Casa"
               data-component="body">
    <br>
<p>Complemento do endereco. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Casa</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="city"                data-endpoint="POSTapi-inscricoes"
               value="Vitoria da Conquista"
               data-component="body">
    <br>
<p>Cidade residencial. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Vitoria da Conquista</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>number</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="number"                data-endpoint="POSTapi-inscricoes"
               value="100"
               data-component="body">
    <br>
<p>Numero do endereco. Must be at least 1. Example: <code>100</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>accepted_terms</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-inscricoes" style="display: none">
            <input type="radio" name="accepted_terms"
                   value="true"
                   data-endpoint="POSTapi-inscricoes"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-inscricoes" style="display: none">
            <input type="radio" name="accepted_terms"
                   value="false"
                   data-endpoint="POSTapi-inscricoes"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Aceite do primeiro termo. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>accepted_terms_2</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-inscricoes" style="display: none">
            <input type="radio" name="accepted_terms_2"
                   value="true"
                   data-endpoint="POSTapi-inscricoes"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-inscricoes" style="display: none">
            <input type="radio" name="accepted_terms_2"
                   value="false"
                   data-endpoint="POSTapi-inscricoes"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Aceite do segundo termo. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-inscricoes"
               value=""
               data-component="body">
    <br>
<p>Campo controlado pelo sistema. Nao envie este campo.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>observation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="observation"                data-endpoint="POSTapi-inscricoes"
               value=""
               data-component="body">
    <br>
<p>Campo controlado pelo sistema na criacao. Nao envie este campo.</p>
        </div>
        </form>

                    <h2 id="inscricoes-GETapi-inscricoes--inscricao-">Exibir inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados de uma inscricao especifica.</p>

<span id="example-requests-GETapi-inscricoes--inscricao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/inscricoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-inscricoes--inscricao-">
    </span>
<span id="execution-results-GETapi-inscricoes--inscricao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-inscricoes--inscricao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-inscricoes--inscricao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-inscricoes--inscricao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-inscricoes--inscricao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-inscricoes--inscricao-" data-method="GET"
      data-path="api/inscricoes/{inscricao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-inscricoes--inscricao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-inscricoes--inscricao-"
                    onclick="tryItOut('GETapi-inscricoes--inscricao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-inscricoes--inscricao-"
                    onclick="cancelTryOut('GETapi-inscricoes--inscricao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-inscricoes--inscricao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/inscricoes/{inscricao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-inscricoes--inscricao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-inscricoes--inscricao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-inscricoes--inscricao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="GETapi-inscricoes--inscricao-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="inscricoes-PUTapi-inscricoes--inscricao-">Atualizar inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza uma inscricao existente quando ela ainda nao esta em analise.</p>

<span id="example-requests-PUTapi-inscricoes--inscricao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/inscricoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Joao da Silva\",
    \"cpf\": \"12345678901\",
    \"rg\": \"12345678\",
    \"father_name\": \"Jose da Silva\",
    \"mother_name\": \"Maria da Silva\",
    \"birth_date\": \"2005-08-15\",
    \"phone\": \"77999999999\",
    \"email\": \"joao@example.com\",
    \"cep\": \"45000000\",
    \"address\": \"Rua Principal\",
    \"neighborhood\": \"Centro\",
    \"complement\": \"Casa\",
    \"city\": \"Vitoria da Conquista\",
    \"number\": 100,
    \"accepted_terms\": true,
    \"accepted_terms_2\": true,
    \"observation\": \"Dados conferidos pela equipe.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Joao da Silva",
    "cpf": "12345678901",
    "rg": "12345678",
    "father_name": "Jose da Silva",
    "mother_name": "Maria da Silva",
    "birth_date": "2005-08-15",
    "phone": "77999999999",
    "email": "joao@example.com",
    "cep": "45000000",
    "address": "Rua Principal",
    "neighborhood": "Centro",
    "complement": "Casa",
    "city": "Vitoria da Conquista",
    "number": 100,
    "accepted_terms": true,
    "accepted_terms_2": true,
    "observation": "Dados conferidos pela equipe."
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-inscricoes--inscricao-">
</span>
<span id="execution-results-PUTapi-inscricoes--inscricao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-inscricoes--inscricao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-inscricoes--inscricao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-inscricoes--inscricao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-inscricoes--inscricao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-inscricoes--inscricao-" data-method="PUT"
      data-path="api/inscricoes/{inscricao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-inscricoes--inscricao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-inscricoes--inscricao-"
                    onclick="tryItOut('PUTapi-inscricoes--inscricao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-inscricoes--inscricao-"
                    onclick="cancelTryOut('PUTapi-inscricoes--inscricao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-inscricoes--inscricao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/inscricoes/{inscricao}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/inscricoes/{inscricao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Joao da Silva"
               data-component="body">
    <br>
<p>Nome completo do estudante. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Joao da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cpf</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cpf"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="12345678901"
               data-component="body">
    <br>
<p>CPF do estudante com 11 digitos. Must be 11 characters. Example: <code>12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>rg</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="rg"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="12345678"
               data-component="body">
    <br>
<p>RG do estudante. Must be at least 8 characters. Must not be greater than 11 characters. Example: <code>12345678</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>father_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="father_name"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Jose da Silva"
               data-component="body">
    <br>
<p>Nome do pai. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Jose da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mother_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mother_name"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Maria da Silva"
               data-component="body">
    <br>
<p>Nome da mae. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Maria da Silva</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>birth_date</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="birth_date"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="2005-08-15"
               data-component="body">
    <br>
<p>Data de nascimento no formato AAAA-MM-DD. Must be a valid date. Must be a valid date in the format <code>Y-m-d</code>. Must be a date before <code>today</code>. Example: <code>2005-08-15</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="phone"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="77999999999"
               data-component="body">
    <br>
<p>Telefone para contato. Must not be greater than 15 characters. Example: <code>77999999999</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="joao@example.com"
               data-component="body">
    <br>
<p>E-mail do estudante. Must be a valid email address. Example: <code>joao@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cep</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cep"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="45000000"
               data-component="body">
    <br>
<p>CEP com 8 digitos. Must be 8 characters. Example: <code>45000000</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Rua Principal"
               data-component="body">
    <br>
<p>Endereco residencial. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Rua Principal</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>neighborhood</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="neighborhood"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Centro"
               data-component="body">
    <br>
<p>Bairro residencial. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Centro</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>complement</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="complement"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Casa"
               data-component="body">
    <br>
<p>Complemento do endereco. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Casa</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="city"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Vitoria da Conquista"
               data-component="body">
    <br>
<p>Cidade residencial. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Vitoria da Conquista</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>number</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="number"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="100"
               data-component="body">
    <br>
<p>Numero do endereco. Must be at least 1. Example: <code>100</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>accepted_terms</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-inscricoes--inscricao-" style="display: none">
            <input type="radio" name="accepted_terms"
                   value="true"
                   data-endpoint="PUTapi-inscricoes--inscricao-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-inscricoes--inscricao-" style="display: none">
            <input type="radio" name="accepted_terms"
                   value="false"
                   data-endpoint="PUTapi-inscricoes--inscricao-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Aceite do primeiro termo. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>accepted_terms_2</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-inscricoes--inscricao-" style="display: none">
            <input type="radio" name="accepted_terms_2"
                   value="true"
                   data-endpoint="PUTapi-inscricoes--inscricao-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-inscricoes--inscricao-" style="display: none">
            <input type="radio" name="accepted_terms_2"
                   value="false"
                   data-endpoint="PUTapi-inscricoes--inscricao-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Aceite do segundo termo. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value=""
               data-component="body">
    <br>
<p>Campo controlado pelo sistema. Nao envie este campo.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>observation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="observation"                data-endpoint="PUTapi-inscricoes--inscricao-"
               value="Dados conferidos pela equipe."
               data-component="body">
    <br>
<p>Observacao da analise ou ajuste cadastral. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Dados conferidos pela equipe.</code></p>
        </div>
        </form>

                    <h2 id="inscricoes-DELETEapi-inscricoes--inscricao-">Remover inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove uma inscricao cadastrada.</p>

<span id="example-requests-DELETEapi-inscricoes--inscricao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/inscricoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-inscricoes--inscricao-">
</span>
<span id="execution-results-DELETEapi-inscricoes--inscricao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-inscricoes--inscricao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-inscricoes--inscricao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-inscricoes--inscricao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-inscricoes--inscricao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-inscricoes--inscricao-" data-method="DELETE"
      data-path="api/inscricoes/{inscricao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-inscricoes--inscricao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-inscricoes--inscricao-"
                    onclick="tryItOut('DELETEapi-inscricoes--inscricao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-inscricoes--inscricao-"
                    onclick="cancelTryOut('DELETEapi-inscricoes--inscricao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-inscricoes--inscricao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/inscricoes/{inscricao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-inscricoes--inscricao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-inscricoes--inscricao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-inscricoes--inscricao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="DELETEapi-inscricoes--inscricao-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="documentos-da-inscricao">Documentos da inscricao</h1>

    <p>Rotas para envio, consulta, atualizacao e remocao dos documentos da inscricao.</p>

                                <h2 id="documentos-da-inscricao-GETapi-inscricoes--inscricao_id--documentos">Listar documentos da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os documentos enviados para uma inscricao.</p>

<span id="example-requests-GETapi-inscricoes--inscricao_id--documentos">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/inscricoes/16/documentos" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/16/documentos"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-inscricoes--inscricao_id--documentos">
    </span>
<span id="execution-results-GETapi-inscricoes--inscricao_id--documentos" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-inscricoes--inscricao_id--documentos"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-inscricoes--inscricao_id--documentos"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-inscricoes--inscricao_id--documentos" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-inscricoes--inscricao_id--documentos">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-inscricoes--inscricao_id--documentos" data-method="GET"
      data-path="api/inscricoes/{inscricao_id}/documentos"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-inscricoes--inscricao_id--documentos', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-inscricoes--inscricao_id--documentos"
                    onclick="tryItOut('GETapi-inscricoes--inscricao_id--documentos');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-inscricoes--inscricao_id--documentos"
                    onclick="cancelTryOut('GETapi-inscricoes--inscricao_id--documentos');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-inscricoes--inscricao_id--documentos"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/inscricoes/{inscricao_id}/documentos</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-inscricoes--inscricao_id--documentos"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos"
               value="16"
               data-component="url">
    <br>
<p>The ID of the inscricao. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="documentos-da-inscricao-POSTapi-inscricoes--inscricao_id--documentos">Enviar documento da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Envia um documento para a inscricao e recalcula o status.</p>

<span id="example-requests-POSTapi-inscricoes--inscricao_id--documentos">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/inscricoes/16/documentos" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "name=foto"\
    --form "type=imagem"\
    --form "file_path=@/tmp/phpbtaohq1jd32cezkDftI" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/16/documentos"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('name', 'foto');
body.append('type', 'imagem');
body.append('file_path', document.querySelector('input[name="file_path"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-inscricoes--inscricao_id--documentos">
</span>
<span id="execution-results-POSTapi-inscricoes--inscricao_id--documentos" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-inscricoes--inscricao_id--documentos"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-inscricoes--inscricao_id--documentos"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-inscricoes--inscricao_id--documentos" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-inscricoes--inscricao_id--documentos">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-inscricoes--inscricao_id--documentos" data-method="POST"
      data-path="api/inscricoes/{inscricao_id}/documentos"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-inscricoes--inscricao_id--documentos', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-inscricoes--inscricao_id--documentos"
                    onclick="tryItOut('POSTapi-inscricoes--inscricao_id--documentos');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-inscricoes--inscricao_id--documentos"
                    onclick="cancelTryOut('POSTapi-inscricoes--inscricao_id--documentos');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-inscricoes--inscricao_id--documentos"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/inscricoes/{inscricao_id}/documentos</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="16"
               data-component="url">
    <br>
<p>The ID of the inscricao. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="foto"
               data-component="body">
    <br>
<p>Nome do documento exigido na inscricao. Must not be greater than 255 characters. Example: <code>foto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value="imagem"
               data-component="body">
    <br>
<p>Tipo ou categoria do documento. Must not be greater than 100 characters. Example: <code>imagem</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file_path</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="file_path"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value=""
               data-component="body">
    <br>
<p>Arquivo do documento. Must be a file. Must not be greater than 2048 kilobytes. Example: <code>/tmp/phpbtaohq1jd32cezkDftI</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-inscricoes--inscricao_id--documentos"
               value=""
               data-component="body">
    <br>
<p>Campo controlado pelo sistema. Nao envie este campo.</p>
        </div>
        </form>

                    <h2 id="documentos-da-inscricao-GETapi-inscricoes--inscricao_id--documentos--id-">Exibir documento da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna um documento especifico da inscricao.</p>

<span id="example-requests-GETapi-inscricoes--inscricao_id--documentos--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/inscricoes/16/documentos/16" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/16/documentos/16"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-inscricoes--inscricao_id--documentos--id-">
    </span>
<span id="execution-results-GETapi-inscricoes--inscricao_id--documentos--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-inscricoes--inscricao_id--documentos--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-inscricoes--inscricao_id--documentos--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-inscricoes--inscricao_id--documentos--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-inscricoes--inscricao_id--documentos--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-inscricoes--inscricao_id--documentos--id-" data-method="GET"
      data-path="api/inscricoes/{inscricao_id}/documentos/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-inscricoes--inscricao_id--documentos--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-inscricoes--inscricao_id--documentos--id-"
                    onclick="tryItOut('GETapi-inscricoes--inscricao_id--documentos--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-inscricoes--inscricao_id--documentos--id-"
                    onclick="cancelTryOut('GETapi-inscricoes--inscricao_id--documentos--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-inscricoes--inscricao_id--documentos--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/inscricoes/{inscricao_id}/documentos/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the inscricao. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the documento. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>documento</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="documento"                data-endpoint="GETapi-inscricoes--inscricao_id--documentos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do documento. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="documentos-da-inscricao-PUTapi-inscricoes--inscricao_id--documentos--id-">Atualizar documento da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados ou arquivo de um documento da inscricao.</p>

<span id="example-requests-PUTapi-inscricoes--inscricao_id--documentos--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/inscricoes/16/documentos/16" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "name=foto"\
    --form "type=imagem"\
    --form "status=Em analise"\
    --form "file_path=@/tmp/phphipo6shsu7vgf448TaR" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/16/documentos/16"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('name', 'foto');
body.append('type', 'imagem');
body.append('status', 'Em analise');
body.append('file_path', document.querySelector('input[name="file_path"]').files[0]);

fetch(url, {
    method: "PUT",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-inscricoes--inscricao_id--documentos--id-">
</span>
<span id="execution-results-PUTapi-inscricoes--inscricao_id--documentos--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-inscricoes--inscricao_id--documentos--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-inscricoes--inscricao_id--documentos--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-inscricoes--inscricao_id--documentos--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-inscricoes--inscricao_id--documentos--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-inscricoes--inscricao_id--documentos--id-" data-method="PUT"
      data-path="api/inscricoes/{inscricao_id}/documentos/{id}"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-inscricoes--inscricao_id--documentos--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-inscricoes--inscricao_id--documentos--id-"
                    onclick="tryItOut('PUTapi-inscricoes--inscricao_id--documentos--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-inscricoes--inscricao_id--documentos--id-"
                    onclick="cancelTryOut('PUTapi-inscricoes--inscricao_id--documentos--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-inscricoes--inscricao_id--documentos--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/inscricoes/{inscricao_id}/documentos/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/inscricoes/{inscricao_id}/documentos/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the inscricao. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the documento. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>documento</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="documento"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do documento. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="foto"
               data-component="body">
    <br>
<p>Nome do documento exigido na inscricao. Must not be greater than 255 characters. Example: <code>foto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="imagem"
               data-component="body">
    <br>
<p>Tipo ou categoria do documento. Must not be greater than 100 characters. Example: <code>imagem</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file_path</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="file_path"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value=""
               data-component="body">
    <br>
<p>Novo arquivo do documento. Must be a file. Must not be greater than 2048 kilobytes. Example: <code>/tmp/phphipo6shsu7vgf448TaR</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-inscricoes--inscricao_id--documentos--id-"
               value="Em analise"
               data-component="body">
    <br>
<p>Status de analise do documento. Must not be greater than 255 characters. Example: <code>Em analise</code></p>
        </div>
        </form>

                    <h2 id="documentos-da-inscricao-DELETEapi-inscricoes--inscricao_id--documentos--id-">Remover documento da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove um documento e o arquivo associado.</p>

<span id="example-requests-DELETEapi-inscricoes--inscricao_id--documentos--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/inscricoes/16/documentos/16" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/16/documentos/16"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-inscricoes--inscricao_id--documentos--id-">
</span>
<span id="execution-results-DELETEapi-inscricoes--inscricao_id--documentos--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-inscricoes--inscricao_id--documentos--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-inscricoes--inscricao_id--documentos--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-inscricoes--inscricao_id--documentos--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-inscricoes--inscricao_id--documentos--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-inscricoes--inscricao_id--documentos--id-" data-method="DELETE"
      data-path="api/inscricoes/{inscricao_id}/documentos/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-inscricoes--inscricao_id--documentos--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-inscricoes--inscricao_id--documentos--id-"
                    onclick="tryItOut('DELETEapi-inscricoes--inscricao_id--documentos--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-inscricoes--inscricao_id--documentos--id-"
                    onclick="cancelTryOut('DELETEapi-inscricoes--inscricao_id--documentos--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-inscricoes--inscricao_id--documentos--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/inscricoes/{inscricao_id}/documentos/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the inscricao. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="16"
               data-component="url">
    <br>
<p>The ID of the documento. Example: <code>16</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao"                data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>documento</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="documento"                data-endpoint="DELETEapi-inscricoes--inscricao_id--documentos--id-"
               value="1"
               data-component="url">
    <br>
<p>ID do documento. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="instituicoes-da-inscricao">Instituicoes da inscricao</h1>

    <p>Rotas para gerenciar os dados academicos vinculados a uma inscricao.</p>

                                <h2 id="instituicoes-da-inscricao-GETapi-inscricoes--inscricao_id--instituicoes">Listar instituicoes da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados academicos vinculados a uma inscricao.</p>

<span id="example-requests-GETapi-inscricoes--inscricao_id--instituicoes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/inscricoes/1/instituicoes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-inscricoes--inscricao_id--instituicoes">
    </span>
<span id="execution-results-GETapi-inscricoes--inscricao_id--instituicoes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-inscricoes--inscricao_id--instituicoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-inscricoes--inscricao_id--instituicoes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-inscricoes--inscricao_id--instituicoes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-inscricoes--inscricao_id--instituicoes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-inscricoes--inscricao_id--instituicoes" data-method="GET"
      data-path="api/inscricoes/{inscricao_id}/instituicoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-inscricoes--inscricao_id--instituicoes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-inscricoes--inscricao_id--instituicoes"
                    onclick="tryItOut('GETapi-inscricoes--inscricao_id--instituicoes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-inscricoes--inscricao_id--instituicoes"
                    onclick="cancelTryOut('GETapi-inscricoes--inscricao_id--instituicoes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-inscricoes--inscricao_id--instituicoes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/inscricoes/{inscricao_id}/instituicoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="instituicoes-da-inscricao-POSTapi-inscricoes--inscricao_id--instituicoes">Cadastrar instituicao na inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Registra os dados academicos de uma inscricao e recalcula seu status.</p>

<span id="example-requests-POSTapi-inscricoes--inscricao_id--instituicoes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"course\": \"Engenharia Civil\",
    \"semester\": \"4\",
    \"expected_completion\": \"2027-12-20\",
    \"instituicao_id\": 1,
    \"shift\": 1,
    \"city_destination\": \"Vitoria da Conquista\",
    \"used_transport\": true,
    \"days_of_week\": [
        1
    ],
    \"has_scholarship\": false,
    \"scholarship_type\": \"Bolsa integral\",
    \"inscricao_id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "course": "Engenharia Civil",
    "semester": "4",
    "expected_completion": "2027-12-20",
    "instituicao_id": 1,
    "shift": 1,
    "city_destination": "Vitoria da Conquista",
    "used_transport": true,
    "days_of_week": [
        1
    ],
    "has_scholarship": false,
    "scholarship_type": "Bolsa integral",
    "inscricao_id": 1
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-inscricoes--inscricao_id--instituicoes">
</span>
<span id="execution-results-POSTapi-inscricoes--inscricao_id--instituicoes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-inscricoes--inscricao_id--instituicoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-inscricoes--inscricao_id--instituicoes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-inscricoes--inscricao_id--instituicoes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-inscricoes--inscricao_id--instituicoes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-inscricoes--inscricao_id--instituicoes" data-method="POST"
      data-path="api/inscricoes/{inscricao_id}/instituicoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-inscricoes--inscricao_id--instituicoes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-inscricoes--inscricao_id--instituicoes"
                    onclick="tryItOut('POSTapi-inscricoes--inscricao_id--instituicoes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-inscricoes--inscricao_id--instituicoes"
                    onclick="cancelTryOut('POSTapi-inscricoes--inscricao_id--instituicoes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-inscricoes--inscricao_id--instituicoes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/inscricoes/{inscricao_id}/instituicoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>course</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="course"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="Engenharia Civil"
               data-component="body">
    <br>
<p>Curso do estudante. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Engenharia Civil</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>semester</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="semester"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="4"
               data-component="body">
    <br>
<p>Semestre atual. Must be at least 1 character. Must not be greater than 50 characters. Example: <code>4</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>expected_completion</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="expected_completion"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="2027-12-20"
               data-component="body">
    <br>
<p>Data prevista de conclusao. Must be a valid date. Must be a date after or equal to <code>today</code>. Example: <code>2027-12-20</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>instituicao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao_id"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="1"
               data-component="body">
    <br>
<p>ID da instituicao de ensino. The <code>id</code> of an existing record in the instituicoes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>shift</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="shift"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="1"
               data-component="body">
    <br>
<p>Turno do curso: 1 para matutino, 2 para noturno. Example: <code>1</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>1</code></li> <li><code>2</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city_destination</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="city_destination"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="Vitoria da Conquista"
               data-component="body">
    <br>
<p>Cidade de destino do transporte. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Vitoria da Conquista</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>used_transport</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes" style="display: none">
            <input type="radio" name="used_transport"
                   value="true"
                   data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes" style="display: none">
            <input type="radio" name="used_transport"
                   value="false"
                   data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Informa se o estudante usa transporte. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>days_of_week</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="days_of_week[0]"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               data-component="body">
        <input type="number" style="display: none"
               name="days_of_week[1]"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               data-component="body">
    <br>
<p>Dia da semana, de 0 a 6. Must be between 0 and 6.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>has_scholarship</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes" style="display: none">
            <input type="radio" name="has_scholarship"
                   value="true"
                   data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes" style="display: none">
            <input type="radio" name="has_scholarship"
                   value="false"
                   data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Informa se o estudante possui bolsa. Example: <code>false</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>scholarship_type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="scholarship_type"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="Bolsa integral"
               data-component="body">
    <br>
<p>Tipo de bolsa, quando houver. This field is required when <code>has_scholarship</code> is <code>true</code>. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Bolsa integral</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="POSTapi-inscricoes--inscricao_id--instituicoes"
               value="1"
               data-component="body">
    <br>
<p>ID da inscricao vinculada. The <code>id</code> of an existing record in the inscricoes table. Example: <code>1</code></p>
        </div>
        </form>

                    <h2 id="instituicoes-da-inscricao-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-">Exibir instituicao da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados academicos especificos de uma inscricao.</p>

<span id="example-requests-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/inscricoes/1/instituicoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-">
    </span>
<span id="execution-results-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-" data-method="GET"
      data-path="api/inscricoes/{inscricao_id}/instituicoes/{instituicao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-inscricoes--inscricao_id--instituicoes--instituicao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    onclick="tryItOut('GETapi-inscricoes--inscricao_id--instituicoes--instituicao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    onclick="cancelTryOut('GETapi-inscricoes--inscricao_id--instituicoes--instituicao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/inscricoes/{inscricao_id}/instituicoes/{instituicao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>instituicao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao"                data-endpoint="GETapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="url">
    <br>
<p>ID do vinculo academico. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="instituicoes-da-inscricao-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-">Atualizar instituicao da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados academicos vinculados a inscricao e recalcula o status.</p>

<span id="example-requests-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"course\": \"Engenharia Civil\",
    \"semester\": \"4\",
    \"expected_completion\": \"2027-12-20\",
    \"instituicao_id\": 1,
    \"shift\": 1,
    \"city_destination\": \"Vitoria da Conquista\",
    \"used_transport\": true,
    \"days_of_week\": [
        1
    ],
    \"has_scholarship\": false,
    \"scholarship_type\": \"Bolsa integral\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "course": "Engenharia Civil",
    "semester": "4",
    "expected_completion": "2027-12-20",
    "instituicao_id": 1,
    "shift": 1,
    "city_destination": "Vitoria da Conquista",
    "used_transport": true,
    "days_of_week": [
        1
    ],
    "has_scholarship": false,
    "scholarship_type": "Bolsa integral"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-">
</span>
<span id="execution-results-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" data-method="PUT"
      data-path="api/inscricoes/{inscricao_id}/instituicoes/{instituicao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    onclick="tryItOut('PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    onclick="cancelTryOut('PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/inscricoes/{inscricao_id}/instituicoes/{instituicao}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/inscricoes/{inscricao_id}/instituicoes/{instituicao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>instituicao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="url">
    <br>
<p>ID do vinculo academico. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>course</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="course"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="Engenharia Civil"
               data-component="body">
    <br>
<p>Curso do estudante. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Engenharia Civil</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>semester</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="semester"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="4"
               data-component="body">
    <br>
<p>Semestre atual. Must be at least 1 character. Must not be greater than 50 characters. Example: <code>4</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>expected_completion</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="expected_completion"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="2027-12-20"
               data-component="body">
    <br>
<p>Data prevista de conclusao. Must be a valid date. Must be a date after or equal to <code>today</code>. Example: <code>2027-12-20</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>instituicao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao_id"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="body">
    <br>
<p>ID da instituicao de ensino. The <code>id</code> of an existing record in the instituicoes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>shift</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="shift"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="body">
    <br>
<p>Turno do curso: 1 para matutino, 2 para noturno. Example: <code>1</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>1</code></li> <li><code>2</code></li></ul>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city_destination</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="city_destination"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="Vitoria da Conquista"
               data-component="body">
    <br>
<p>Cidade de destino do transporte. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Vitoria da Conquista</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>used_transport</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" style="display: none">
            <input type="radio" name="used_transport"
                   value="true"
                   data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" style="display: none">
            <input type="radio" name="used_transport"
                   value="false"
                   data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Informa se o estudante usa transporte. Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>days_of_week</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="days_of_week[0]"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               data-component="body">
        <input type="number" style="display: none"
               name="days_of_week[1]"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               data-component="body">
    <br>
<p>Dia da semana, de 0 a 6. Must be between 0 and 6.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>has_scholarship</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" style="display: none">
            <input type="radio" name="has_scholarship"
                   value="true"
                   data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-" style="display: none">
            <input type="radio" name="has_scholarship"
                   value="false"
                   data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Informa se o estudante possui bolsa. Example: <code>false</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>scholarship_type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="scholarship_type"                data-endpoint="PUTapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="Bolsa integral"
               data-component="body">
    <br>
<p>Tipo de bolsa, quando houver. This field is required when <code>has_scholarship</code> is <code>true</code>. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Bolsa integral</code></p>
        </div>
        </form>

                    <h2 id="instituicoes-da-inscricao-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-">Remover instituicao da inscricao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove os dados academicos vinculados a uma inscricao.</p>

<span id="example-requests-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/inscricoes/1/instituicoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-">
</span>
<span id="execution-results-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-" data-method="DELETE"
      data-path="api/inscricoes/{inscricao_id}/instituicoes/{instituicao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    onclick="tryItOut('DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    onclick="cancelTryOut('DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/inscricoes/{inscricao_id}/instituicoes/{instituicao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>inscricao_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="inscricao_id"                data-endpoint="DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="url">
    <br>
<p>ID da inscricao. Example: <code>1</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>instituicao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao"                data-endpoint="DELETEapi-inscricoes--inscricao_id--instituicoes--instituicao-"
               value="1"
               data-component="url">
    <br>
<p>ID do vinculo academico. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="instituicoes">Instituicoes</h1>

    <p>Rotas para gerenciamento das instituicoes de ensino.</p>

                                <h2 id="instituicoes-GETapi-instituicao">Listar instituicoes.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna a lista paginada de instituicoes cadastradas.</p>

<span id="example-requests-GETapi-instituicao">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/instituicao" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/instituicao"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-instituicao">
    </span>
<span id="execution-results-GETapi-instituicao" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-instituicao"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-instituicao"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-instituicao" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-instituicao">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-instituicao" data-method="GET"
      data-path="api/instituicao"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-instituicao', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-instituicao"
                    onclick="tryItOut('GETapi-instituicao');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-instituicao"
                    onclick="cancelTryOut('GETapi-instituicao');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-instituicao"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/instituicao</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-instituicao"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-instituicao"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-instituicao"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="instituicoes-POSTapi-instituicao">Cadastrar instituicao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria uma nova instituicao de ensino.</p>

<span id="example-requests-POSTapi-instituicao">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/instituicao" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Universidade Estadual\",
    \"linhas_ids\": [
        1
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/instituicao"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Universidade Estadual",
    "linhas_ids": [
        1
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-instituicao">
</span>
<span id="execution-results-POSTapi-instituicao" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-instituicao"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-instituicao"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-instituicao" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-instituicao">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-instituicao" data-method="POST"
      data-path="api/instituicao"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-instituicao', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-instituicao"
                    onclick="tryItOut('POSTapi-instituicao');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-instituicao"
                    onclick="cancelTryOut('POSTapi-instituicao');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-instituicao"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/instituicao</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-instituicao"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-instituicao"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-instituicao"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-instituicao"
               value="Universidade Estadual"
               data-component="body">
    <br>
<p>Nome da instituicao. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Universidade Estadual</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>linhas_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linhas_ids[0]"                data-endpoint="POSTapi-instituicao"
               data-component="body">
        <input type="number" style="display: none"
               name="linhas_ids[1]"                data-endpoint="POSTapi-instituicao"
               data-component="body">
    <br>
<p>ID de uma linha vinculada.</p>
        </div>
        </form>

                    <h2 id="instituicoes-GETapi-instituicao--id-">Exibir instituicao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados de uma instituicao especifica.</p>

<span id="example-requests-GETapi-instituicao--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/instituicao/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/instituicao/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-instituicao--id-">
    </span>
<span id="execution-results-GETapi-instituicao--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-instituicao--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-instituicao--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-instituicao--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-instituicao--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-instituicao--id-" data-method="GET"
      data-path="api/instituicao/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-instituicao--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-instituicao--id-"
                    onclick="tryItOut('GETapi-instituicao--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-instituicao--id-"
                    onclick="cancelTryOut('GETapi-instituicao--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-instituicao--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/instituicao/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-instituicao--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-instituicao--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-instituicao--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-instituicao--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the instituicao. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>instituicao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao"                data-endpoint="GETapi-instituicao--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da instituicao. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="instituicoes-PUTapi-instituicao--id-">Atualizar instituicao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de uma instituicao existente.</p>

<span id="example-requests-PUTapi-instituicao--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/instituicao/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Universidade Estadual\",
    \"linhas_ids\": [
        1
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/instituicao/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Universidade Estadual",
    "linhas_ids": [
        1
    ]
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-instituicao--id-">
</span>
<span id="execution-results-PUTapi-instituicao--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-instituicao--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-instituicao--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-instituicao--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-instituicao--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-instituicao--id-" data-method="PUT"
      data-path="api/instituicao/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-instituicao--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-instituicao--id-"
                    onclick="tryItOut('PUTapi-instituicao--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-instituicao--id-"
                    onclick="cancelTryOut('PUTapi-instituicao--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-instituicao--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/instituicao/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/instituicao/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-instituicao--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-instituicao--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-instituicao--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PUTapi-instituicao--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the instituicao. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>instituicao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao"                data-endpoint="PUTapi-instituicao--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da instituicao. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-instituicao--id-"
               value="Universidade Estadual"
               data-component="body">
    <br>
<p>Nome da instituicao. Must be at least 3 characters. Must not be greater than 255 characters. Example: <code>Universidade Estadual</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>linhas_ids</code></b>&nbsp;&nbsp;
<small>integer[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linhas_ids[0]"                data-endpoint="PUTapi-instituicao--id-"
               data-component="body">
        <input type="number" style="display: none"
               name="linhas_ids[1]"                data-endpoint="PUTapi-instituicao--id-"
               data-component="body">
    <br>
<p>ID de uma linha vinculada.</p>
        </div>
        </form>

                    <h2 id="instituicoes-DELETEapi-instituicao--id-">Remover instituicao.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove uma instituicao cadastrada.</p>

<span id="example-requests-DELETEapi-instituicao--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/instituicao/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/instituicao/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-instituicao--id-">
</span>
<span id="execution-results-DELETEapi-instituicao--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-instituicao--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-instituicao--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-instituicao--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-instituicao--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-instituicao--id-" data-method="DELETE"
      data-path="api/instituicao/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-instituicao--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-instituicao--id-"
                    onclick="tryItOut('DELETEapi-instituicao--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-instituicao--id-"
                    onclick="cancelTryOut('DELETEapi-instituicao--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-instituicao--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/instituicao/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-instituicao--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-instituicao--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-instituicao--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-instituicao--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the instituicao. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>instituicao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="instituicao"                data-endpoint="DELETEapi-instituicao--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da instituicao. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="linhas">Linhas</h1>

    <p>Rotas para gerenciamento das linhas de transporte.</p>

                                <h2 id="linhas-GETapi-linha">Listar linhas.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna todas as linhas cadastradas.</p>

<span id="example-requests-GETapi-linha">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/linha" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/linha"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-linha">
    </span>
<span id="execution-results-GETapi-linha" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-linha"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-linha"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-linha" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-linha">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-linha" data-method="GET"
      data-path="api/linha"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-linha', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-linha"
                    onclick="tryItOut('GETapi-linha');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-linha"
                    onclick="cancelTryOut('GETapi-linha');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-linha"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/linha</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-linha"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-linha"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-linha"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="linhas-POSTapi-linha">Cadastrar linha.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria uma nova linha de transporte.</p>

<span id="example-requests-POSTapi-linha">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/linha" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Linha Centro\",
    \"description\": \"Rota principal ate o centro\",
    \"departure_time\": \"07:00\",
    \"return_time\": \"18:00\",
    \"max_capacity\": 40
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/linha"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Linha Centro",
    "description": "Rota principal ate o centro",
    "departure_time": "07:00",
    "return_time": "18:00",
    "max_capacity": 40
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-linha">
</span>
<span id="execution-results-POSTapi-linha" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-linha"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-linha"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-linha" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-linha">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-linha" data-method="POST"
      data-path="api/linha"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-linha', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-linha"
                    onclick="tryItOut('POSTapi-linha');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-linha"
                    onclick="cancelTryOut('POSTapi-linha');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-linha"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/linha</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-linha"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-linha"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-linha"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-linha"
               value="Linha Centro"
               data-component="body">
    <br>
<p>Nome da linha. Must not be greater than 255 characters. Must be at least 3 characters. Example: <code>Linha Centro</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="POSTapi-linha"
               value="Rota principal ate o centro"
               data-component="body">
    <br>
<p>Descricao da rota da linha. Must not be greater than 255 characters. Must be at least 3 characters. Example: <code>Rota principal ate o centro</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>departure_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="departure_time"                data-endpoint="POSTapi-linha"
               value="07:00"
               data-component="body">
    <br>
<p>Horario de saida no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Example: <code>07:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>return_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="return_time"                data-endpoint="POSTapi-linha"
               value="18:00"
               data-component="body">
    <br>
<p>Horario de retorno no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Must be a date after <code>departure_time</code>. Example: <code>18:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>max_capacity</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="max_capacity"                data-endpoint="POSTapi-linha"
               value="40"
               data-component="body">
    <br>
<p>Capacidade maxima de estudantes. Example: <code>40</code></p>
        </div>
        </form>

                    <h2 id="linhas-GETapi-linha--id-">Exibir linha.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna os dados de uma linha especifica.</p>

<span id="example-requests-GETapi-linha--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/linha/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/linha/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-linha--id-">
    </span>
<span id="execution-results-GETapi-linha--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-linha--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-linha--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-linha--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-linha--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-linha--id-" data-method="GET"
      data-path="api/linha/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-linha--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-linha--id-"
                    onclick="tryItOut('GETapi-linha--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-linha--id-"
                    onclick="cancelTryOut('GETapi-linha--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-linha--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/linha/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-linha--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-linha--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-linha--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-linha--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the linha. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>linha</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linha"                data-endpoint="GETapi-linha--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da linha. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="linhas-PUTapi-linha--id-">Atualizar linha.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de uma linha existente.</p>

<span id="example-requests-PUTapi-linha--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/linha/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Linha Centro\",
    \"description\": \"Rota principal ate o centro\",
    \"departure_time\": \"07:00\",
    \"return_time\": \"18:00\",
    \"max_capacity\": 40
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/linha/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Linha Centro",
    "description": "Rota principal ate o centro",
    "departure_time": "07:00",
    "return_time": "18:00",
    "max_capacity": 40
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-linha--id-">
</span>
<span id="execution-results-PUTapi-linha--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-linha--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-linha--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-linha--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-linha--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-linha--id-" data-method="PUT"
      data-path="api/linha/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-linha--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-linha--id-"
                    onclick="tryItOut('PUTapi-linha--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-linha--id-"
                    onclick="cancelTryOut('PUTapi-linha--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-linha--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/linha/{id}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/linha/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-linha--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-linha--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-linha--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PUTapi-linha--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the linha. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>linha</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linha"                data-endpoint="PUTapi-linha--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da linha. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PUTapi-linha--id-"
               value="Linha Centro"
               data-component="body">
    <br>
<p>Nome da linha. Must not be greater than 255 characters. Must be at least 3 characters. Example: <code>Linha Centro</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description"                data-endpoint="PUTapi-linha--id-"
               value="Rota principal ate o centro"
               data-component="body">
    <br>
<p>Descricao da rota da linha. Must not be greater than 255 characters. Must be at least 3 characters. Example: <code>Rota principal ate o centro</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>departure_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="departure_time"                data-endpoint="PUTapi-linha--id-"
               value="07:00"
               data-component="body">
    <br>
<p>Horario de saida no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Example: <code>07:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>return_time</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="return_time"                data-endpoint="PUTapi-linha--id-"
               value="18:00"
               data-component="body">
    <br>
<p>Horario de retorno no formato HH:MM. Must be a valid date in the format <code>H:i</code>. Must be a date after <code>departure_time</code>. Example: <code>18:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>max_capacity</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="max_capacity"                data-endpoint="PUTapi-linha--id-"
               value="40"
               data-component="body">
    <br>
<p>Capacidade maxima de estudantes. Example: <code>40</code></p>
        </div>
        </form>

                    <h2 id="linhas-DELETEapi-linha--id-">Remover linha.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove uma linha cadastrada.</p>

<span id="example-requests-DELETEapi-linha--id-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/linha/architecto" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/linha/architecto"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-linha--id-">
</span>
<span id="execution-results-DELETEapi-linha--id-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-linha--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-linha--id-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-linha--id-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-linha--id-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-linha--id-" data-method="DELETE"
      data-path="api/linha/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-linha--id-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-linha--id-"
                    onclick="tryItOut('DELETEapi-linha--id-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-linha--id-"
                    onclick="cancelTryOut('DELETEapi-linha--id-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-linha--id-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/linha/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-linha--id-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-linha--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-linha--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="DELETEapi-linha--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the linha. Example: <code>architecto</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>linha</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="linha"                data-endpoint="DELETEapi-linha--id-"
               value="1"
               data-component="url">
    <br>
<p>ID da linha. Example: <code>1</code></p>
            </div>
                    </form>

                <h1 id="recadastro">Recadastro</h1>

    <p>Rotas para documentos e solicitacoes de recadastro dos estudantes.</p>

                                <h2 id="recadastro-GETapi-estudantes-reecadastrar">Listar documentos de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna todos os documentos enviados para recadastro.</p>

<span id="example-requests-GETapi-estudantes-reecadastrar">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/estudantes/reecadastrar" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/reecadastrar"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-estudantes-reecadastrar">
    </span>
<span id="execution-results-GETapi-estudantes-reecadastrar" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-estudantes-reecadastrar"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-estudantes-reecadastrar"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-estudantes-reecadastrar" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-estudantes-reecadastrar">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-estudantes-reecadastrar" data-method="GET"
      data-path="api/estudantes/reecadastrar"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-estudantes-reecadastrar', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-estudantes-reecadastrar"
                    onclick="tryItOut('GETapi-estudantes-reecadastrar');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-estudantes-reecadastrar"
                    onclick="cancelTryOut('GETapi-estudantes-reecadastrar');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-estudantes-reecadastrar"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/estudantes/reecadastrar</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-estudantes-reecadastrar"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-estudantes-reecadastrar"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-estudantes-reecadastrar"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="recadastro-POSTapi-estudantes-reecadastrar">Cadastrar documento de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Registra um documento vinculado ao recadastro de um estudante.</p>

<span id="example-requests-POSTapi-estudantes-reecadastrar">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/estudantes/reecadastrar" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"estudante_id\": 1,
    \"type\": \"comprovante_residencia\",
    \"file_path\": \"documentos\\/arquivo.pdf\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/reecadastrar"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "estudante_id": 1,
    "type": "comprovante_residencia",
    "file_path": "documentos\/arquivo.pdf"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-estudantes-reecadastrar">
</span>
<span id="execution-results-POSTapi-estudantes-reecadastrar" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-estudantes-reecadastrar"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-estudantes-reecadastrar"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-estudantes-reecadastrar" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-estudantes-reecadastrar">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-estudantes-reecadastrar" data-method="POST"
      data-path="api/estudantes/reecadastrar"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-estudantes-reecadastrar', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-estudantes-reecadastrar"
                    onclick="tryItOut('POSTapi-estudantes-reecadastrar');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-estudantes-reecadastrar"
                    onclick="cancelTryOut('POSTapi-estudantes-reecadastrar');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-estudantes-reecadastrar"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/estudantes/reecadastrar</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-estudantes-reecadastrar"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-estudantes-reecadastrar"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-estudantes-reecadastrar"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>estudante_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="estudante_id"                data-endpoint="POSTapi-estudantes-reecadastrar"
               value="1"
               data-component="body">
    <br>
<p>ID do estudante vinculado ao recadastro. The <code>id</code> of an existing record in the estudantes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="POSTapi-estudantes-reecadastrar"
               value="comprovante_residencia"
               data-component="body">
    <br>
<p>Tipo do documento de recadastro. Example: <code>comprovante_residencia</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file_path</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="file_path"                data-endpoint="POSTapi-estudantes-reecadastrar"
               value="documentos/arquivo.pdf"
               data-component="body">
    <br>
<p>Caminho ou identificador do arquivo enviado. Example: <code>documentos/arquivo.pdf</code></p>
        </div>
        </form>

                    <h2 id="recadastro-GETapi-estudantes-reecadastrar--documento-">Exibir documento de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna um documento de recadastro especifico.</p>

<span id="example-requests-GETapi-estudantes-reecadastrar--documento-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/estudantes/reecadastrar/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/reecadastrar/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-estudantes-reecadastrar--documento-">
    </span>
<span id="execution-results-GETapi-estudantes-reecadastrar--documento-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-estudantes-reecadastrar--documento-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-estudantes-reecadastrar--documento-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-estudantes-reecadastrar--documento-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-estudantes-reecadastrar--documento-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-estudantes-reecadastrar--documento-" data-method="GET"
      data-path="api/estudantes/reecadastrar/{documento}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-estudantes-reecadastrar--documento-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-estudantes-reecadastrar--documento-"
                    onclick="tryItOut('GETapi-estudantes-reecadastrar--documento-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-estudantes-reecadastrar--documento-"
                    onclick="cancelTryOut('GETapi-estudantes-reecadastrar--documento-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-estudantes-reecadastrar--documento-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/estudantes/reecadastrar/{documento}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-estudantes-reecadastrar--documento-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-estudantes-reecadastrar--documento-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-estudantes-reecadastrar--documento-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>documento</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="documento"                data-endpoint="GETapi-estudantes-reecadastrar--documento-"
               value="1"
               data-component="url">
    <br>
<p>ID do documento de recadastro. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="recadastro-PUTapi-estudantes-reecadastrar--documento-">Atualizar documento de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de um documento de recadastro.</p>

<span id="example-requests-PUTapi-estudantes-reecadastrar--documento-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/estudantes/reecadastrar/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"estudante_id\": 1,
    \"type\": \"comprovante_residencia\",
    \"file_path\": \"documentos\\/arquivo.pdf\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/reecadastrar/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "estudante_id": 1,
    "type": "comprovante_residencia",
    "file_path": "documentos\/arquivo.pdf"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-estudantes-reecadastrar--documento-">
</span>
<span id="execution-results-PUTapi-estudantes-reecadastrar--documento-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-estudantes-reecadastrar--documento-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-estudantes-reecadastrar--documento-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-estudantes-reecadastrar--documento-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-estudantes-reecadastrar--documento-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-estudantes-reecadastrar--documento-" data-method="PUT"
      data-path="api/estudantes/reecadastrar/{documento}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-estudantes-reecadastrar--documento-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-estudantes-reecadastrar--documento-"
                    onclick="tryItOut('PUTapi-estudantes-reecadastrar--documento-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-estudantes-reecadastrar--documento-"
                    onclick="cancelTryOut('PUTapi-estudantes-reecadastrar--documento-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-estudantes-reecadastrar--documento-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/estudantes/reecadastrar/{documento}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/estudantes/reecadastrar/{documento}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>documento</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="documento"                data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="1"
               data-component="url">
    <br>
<p>ID do documento de recadastro. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>estudante_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="estudante_id"                data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="1"
               data-component="body">
    <br>
<p>ID do estudante vinculado ao recadastro. The <code>id</code> of an existing record in the estudantes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="comprovante_residencia"
               data-component="body">
    <br>
<p>Tipo do documento de recadastro. Example: <code>comprovante_residencia</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file_path</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="file_path"                data-endpoint="PUTapi-estudantes-reecadastrar--documento-"
               value="documentos/arquivo.pdf"
               data-component="body">
    <br>
<p>Caminho ou identificador do arquivo enviado. Example: <code>documentos/arquivo.pdf</code></p>
        </div>
        </form>

                    <h2 id="recadastro-DELETEapi-estudantes-reecadastrar--documento-">Remover documento de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove um documento de recadastro.</p>

<span id="example-requests-DELETEapi-estudantes-reecadastrar--documento-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/estudantes/reecadastrar/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/estudantes/reecadastrar/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-estudantes-reecadastrar--documento-">
</span>
<span id="execution-results-DELETEapi-estudantes-reecadastrar--documento-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-estudantes-reecadastrar--documento-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-estudantes-reecadastrar--documento-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-estudantes-reecadastrar--documento-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-estudantes-reecadastrar--documento-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-estudantes-reecadastrar--documento-" data-method="DELETE"
      data-path="api/estudantes/reecadastrar/{documento}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-estudantes-reecadastrar--documento-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-estudantes-reecadastrar--documento-"
                    onclick="tryItOut('DELETEapi-estudantes-reecadastrar--documento-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-estudantes-reecadastrar--documento-"
                    onclick="cancelTryOut('DELETEapi-estudantes-reecadastrar--documento-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-estudantes-reecadastrar--documento-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/estudantes/reecadastrar/{documento}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-estudantes-reecadastrar--documento-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-estudantes-reecadastrar--documento-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-estudantes-reecadastrar--documento-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>documento</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="documento"                data-endpoint="DELETEapi-estudantes-reecadastrar--documento-"
               value="1"
               data-component="url">
    <br>
<p>ID do documento de recadastro. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="recadastro-GETapi-reecadastro-solicitacoes">Listar solicitacoes de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna todas as solicitacoes de recadastro cadastradas.</p>

<span id="example-requests-GETapi-reecadastro-solicitacoes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/reecadastro/solicitacoes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-reecadastro-solicitacoes">
    </span>
<span id="execution-results-GETapi-reecadastro-solicitacoes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-reecadastro-solicitacoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-reecadastro-solicitacoes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-reecadastro-solicitacoes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-reecadastro-solicitacoes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-reecadastro-solicitacoes" data-method="GET"
      data-path="api/reecadastro/solicitacoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-reecadastro-solicitacoes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-reecadastro-solicitacoes"
                    onclick="tryItOut('GETapi-reecadastro-solicitacoes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-reecadastro-solicitacoes"
                    onclick="cancelTryOut('GETapi-reecadastro-solicitacoes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-reecadastro-solicitacoes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/reecadastro/solicitacoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-reecadastro-solicitacoes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-reecadastro-solicitacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-reecadastro-solicitacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="recadastro-POSTapi-reecadastro-solicitacoes">Cadastrar solicitacao de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria uma solicitacao de recadastro para um estudante.</p>

<span id="example-requests-POSTapi-reecadastro-solicitacoes">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"estudante_id\": 1,
    \"status\": \"pendente\",
    \"observacoes\": \"Solicitacao enviada pelo estudante.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "estudante_id": 1,
    "status": "pendente",
    "observacoes": "Solicitacao enviada pelo estudante."
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-reecadastro-solicitacoes">
</span>
<span id="execution-results-POSTapi-reecadastro-solicitacoes" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-POSTapi-reecadastro-solicitacoes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-reecadastro-solicitacoes"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-reecadastro-solicitacoes" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-POSTapi-reecadastro-solicitacoes">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-POSTapi-reecadastro-solicitacoes" data-method="POST"
      data-path="api/reecadastro/solicitacoes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-reecadastro-solicitacoes', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-reecadastro-solicitacoes"
                    onclick="tryItOut('POSTapi-reecadastro-solicitacoes');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-reecadastro-solicitacoes"
                    onclick="cancelTryOut('POSTapi-reecadastro-solicitacoes');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-reecadastro-solicitacoes"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/reecadastro/solicitacoes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-reecadastro-solicitacoes"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-reecadastro-solicitacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-reecadastro-solicitacoes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>estudante_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="estudante_id"                data-endpoint="POSTapi-reecadastro-solicitacoes"
               value="1"
               data-component="body">
    <br>
<p>ID do estudante que solicitou o recadastro. The <code>id</code> of an existing record in the estudantes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="POSTapi-reecadastro-solicitacoes"
               value="pendente"
               data-component="body">
    <br>
<p>Status inicial da solicitacao. Example: <code>pendente</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>observacoes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="observacoes"                data-endpoint="POSTapi-reecadastro-solicitacoes"
               value="Solicitacao enviada pelo estudante."
               data-component="body">
    <br>
<p>Observacoes da solicitacao. Example: <code>Solicitacao enviada pelo estudante.</code></p>
        </div>
        </form>

                    <h2 id="recadastro-GETapi-reecadastro-solicitacoes--solicitacao-">Exibir solicitacao de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Retorna uma solicitacao de recadastro especifica.</p>

<span id="example-requests-GETapi-reecadastro-solicitacoes--solicitacao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://127.0.0.1:8000/api/reecadastro/solicitacoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-reecadastro-solicitacoes--solicitacao-">
    </span>
<span id="execution-results-GETapi-reecadastro-solicitacoes--solicitacao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-GETapi-reecadastro-solicitacoes--solicitacao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-reecadastro-solicitacoes--solicitacao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-reecadastro-solicitacoes--solicitacao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-GETapi-reecadastro-solicitacoes--solicitacao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-GETapi-reecadastro-solicitacoes--solicitacao-" data-method="GET"
      data-path="api/reecadastro/solicitacoes/{solicitacao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-reecadastro-solicitacoes--solicitacao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-reecadastro-solicitacoes--solicitacao-"
                    onclick="tryItOut('GETapi-reecadastro-solicitacoes--solicitacao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-reecadastro-solicitacoes--solicitacao-"
                    onclick="cancelTryOut('GETapi-reecadastro-solicitacoes--solicitacao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-reecadastro-solicitacoes--solicitacao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/reecadastro/solicitacoes/{solicitacao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-reecadastro-solicitacoes--solicitacao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-reecadastro-solicitacoes--solicitacao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-reecadastro-solicitacoes--solicitacao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>solicitacao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="solicitacao"                data-endpoint="GETapi-reecadastro-solicitacoes--solicitacao-"
               value="1"
               data-component="url">
    <br>
<p>ID da solicitacao. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="recadastro-PUTapi-reecadastro-solicitacoes--solicitacao-">Atualizar solicitacao de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atualiza os dados de uma solicitacao de recadastro.</p>

<span id="example-requests-PUTapi-reecadastro-solicitacoes--solicitacao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"estudante_id\": 1,
    \"status\": \"aprovado\",
    \"observacoes\": \"Solicitacao aprovada pela equipe.\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "estudante_id": 1,
    "status": "aprovado",
    "observacoes": "Solicitacao aprovada pela equipe."
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-reecadastro-solicitacoes--solicitacao-">
</span>
<span id="execution-results-PUTapi-reecadastro-solicitacoes--solicitacao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-PUTapi-reecadastro-solicitacoes--solicitacao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-reecadastro-solicitacoes--solicitacao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-reecadastro-solicitacoes--solicitacao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-PUTapi-reecadastro-solicitacoes--solicitacao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-PUTapi-reecadastro-solicitacoes--solicitacao-" data-method="PUT"
      data-path="api/reecadastro/solicitacoes/{solicitacao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-reecadastro-solicitacoes--solicitacao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-reecadastro-solicitacoes--solicitacao-"
                    onclick="tryItOut('PUTapi-reecadastro-solicitacoes--solicitacao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-reecadastro-solicitacoes--solicitacao-"
                    onclick="cancelTryOut('PUTapi-reecadastro-solicitacoes--solicitacao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-reecadastro-solicitacoes--solicitacao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/reecadastro/solicitacoes/{solicitacao}</code></b>
        </p>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/reecadastro/solicitacoes/{solicitacao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>solicitacao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="solicitacao"                data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="1"
               data-component="url">
    <br>
<p>ID da solicitacao. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Parametros do corpo</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>estudante_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="estudante_id"                data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="1"
               data-component="body">
    <br>
<p>ID do estudante que solicitou o recadastro. The <code>id</code> of an existing record in the estudantes table. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="aprovado"
               data-component="body">
    <br>
<p>Status atual da solicitacao. Example: <code>aprovado</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>observacoes</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="observacoes"                data-endpoint="PUTapi-reecadastro-solicitacoes--solicitacao-"
               value="Solicitacao aprovada pela equipe."
               data-component="body">
    <br>
<p>Observacoes da solicitacao. Example: <code>Solicitacao aprovada pela equipe.</code></p>
        </div>
        </form>

                    <h2 id="recadastro-DELETEapi-reecadastro-solicitacoes--solicitacao-">Remover solicitacao de recadastro.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Remove uma solicitacao de recadastro.</p>

<span id="example-requests-DELETEapi-reecadastro-solicitacoes--solicitacao-">
<blockquote>Exemplo de requisicao:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes/1" \
    --header "Authorization: Bearer TOKEN_DE_ACESSO" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://127.0.0.1:8000/api/reecadastro/solicitacoes/1"
);

const headers = {
    "Authorization": "Bearer TOKEN_DE_ACESSO",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-reecadastro-solicitacoes--solicitacao-">
</span>
<span id="execution-results-DELETEapi-reecadastro-solicitacoes--solicitacao-" hidden>
    <blockquote>Resposta recebida<span
                id="execution-response-status-DELETEapi-reecadastro-solicitacoes--solicitacao-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-reecadastro-solicitacoes--solicitacao-"
      data-empty-response-text="<Resposta vazia>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-reecadastro-solicitacoes--solicitacao-" hidden>
    <blockquote>A requisicao falhou com erro:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-reecadastro-solicitacoes--solicitacao-">

Dica: verifique se voce esta conectado a rede.
Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
O console das ferramentas de desenvolvimento pode trazer mais detalhes.</code></pre>
</span>
<form id="form-DELETEapi-reecadastro-solicitacoes--solicitacao-" data-method="DELETE"
      data-path="api/reecadastro/solicitacoes/{solicitacao}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-reecadastro-solicitacoes--solicitacao-', this);">
    <h3>
        Requisicao&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-reecadastro-solicitacoes--solicitacao-"
                    onclick="tryItOut('DELETEapi-reecadastro-solicitacoes--solicitacao-');">Testar requisicao
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-reecadastro-solicitacoes--solicitacao-"
                    onclick="cancelTryOut('DELETEapi-reecadastro-solicitacoes--solicitacao-');" hidden>Cancelar
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-reecadastro-solicitacoes--solicitacao-"
                    data-initial-text="Enviar requisicao"
                    data-loading-text="Enviando..."
                    hidden>Enviar requisicao
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/reecadastro/solicitacoes/{solicitacao}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Cabecalhos</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-reecadastro-solicitacoes--solicitacao-"
               value="Bearer TOKEN_DE_ACESSO"
               data-component="header">
    <br>
<p>Example: <code>Bearer TOKEN_DE_ACESSO</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-reecadastro-solicitacoes--solicitacao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-reecadastro-solicitacoes--solicitacao-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Parametros da URL</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>solicitacao</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="solicitacao"                data-endpoint="DELETEapi-reecadastro-solicitacoes--solicitacao-"
               value="1"
               data-component="url">
    <br>
<p>ID da solicitacao. Example: <code>1</code></p>
            </div>
                    </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
