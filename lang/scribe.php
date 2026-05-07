<?php

return [
    'labels' => [
        'search' => 'Pesquisar',
        'base_url' => 'URL base',
    ],

    'auth' => [
        'none' => 'Esta API nao exige autenticacao.',
        'instruction' => [
            'query' => <<<'TEXT'
                Para autenticar as requisicoes, inclua o parametro **`:parameterName`** na query string.
                TEXT,
            'body' => <<<'TEXT'
                Para autenticar as requisicoes, inclua o parametro **`:parameterName`** no corpo da requisicao.
                TEXT,
            'query_or_body' => <<<'TEXT'
                Para autenticar as requisicoes, inclua o parametro **`:parameterName`** na query string ou no corpo da requisicao.
                TEXT,
            'bearer' => <<<'TEXT'
                Para autenticar as requisicoes, inclua o cabecalho **`Authorization`** com o valor **`"Bearer :placeholder"`**.
                TEXT,
            'basic' => <<<'TEXT'
                Para autenticar as requisicoes, inclua o cabecalho **`Authorization`** no formato **`"Basic {credentials}"`**.
                O valor de `{credentials}` deve ser usuario/id e senha unidos por dois-pontos (:), codificados em base64.
                TEXT,
            'header' => <<<'TEXT'
                Para autenticar as requisicoes, inclua o cabecalho **`:parameterName`** com o valor **`":placeholder"`**.
                TEXT,
        ],
        'details' => <<<'TEXT'
            Todos os endpoints autenticados aparecem marcados com o selo `requer autenticacao` na documentacao abaixo.
            TEXT,
    ],

    'headings' => [
        'introduction' => 'Introducao',
        'auth' => 'Autenticacao das requisicoes',
    ],

    'endpoint' => [
        'request' => 'Requisicao',
        'headers' => 'Cabecalhos',
        'url_parameters' => 'Parametros da URL',
        'body_parameters' => 'Parametros do corpo',
        'query_parameters' => 'Parametros da query',
        'response' => 'Resposta',
        'response_fields' => 'Campos da resposta',
        'example_request' => 'Exemplo de requisicao',
        'example_response' => 'Exemplo de resposta',
        'responses' => [
            'binary' => 'Dados binarios',
            'empty' => 'Resposta vazia',
        ],
    ],

    'try_it_out' => [
        'open' => 'Testar requisicao',
        'cancel' => 'Cancelar',
        'send' => 'Enviar requisicao',
        'loading' => 'Enviando...',
        'received_response' => 'Resposta recebida',
        'request_failed' => 'A requisicao falhou com erro',
        'error_help' => <<<'TEXT'
            Dica: verifique se voce esta conectado a rede.
            Se voce mantem esta API, confirme se ela esta em execucao e se o CORS esta habilitado.
            O console das ferramentas de desenvolvimento pode trazer mais detalhes.
            TEXT,
    ],

    'links' => [
        'postman' => 'Ver colecao Postman',
        'openapi' => 'Ver especificacao OpenAPI',
    ],
];
