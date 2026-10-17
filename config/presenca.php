<?php

/*
|--------------------------------------------------------------------------
| Presenca por QR Code, dispositivo autorizado, horario e geolocalizacao
|--------------------------------------------------------------------------
|
| Parametros do modulo descrito no ROADMAP.md. Varios dependem de decisoes
| ainda abertas com o PO (D01 a D10): por isso ficam aqui, e nao no codigo.
| Nenhum valor abaixo deve ser tratado como calibrado antes do piloto.
|
*/

return [

    /*
     * Fuso da operacao. Horarios de saida, datas de viagem e feriados sao
     * locais; a aplicacao (config/app.php) roda em UTC. Ver App\Support\HorarioLocal.
     */
    'fuso' => env('PRESENCA_FUSO', 'America/Sao_Paulo'),

    /*
     * Endereco gravado no QR fixo de cada van. O token opaco vai no parametro
     * `t`; quem abre a URL e o frontend, que repassa o token a API.
     */
    'qr_url' => env('PRESENCA_QR_URL', rtrim((string) env('FRONTEND_URL', env('APP_URL', 'http://localhost')), '/').'/presenca/scan'),

    /*
     * Janela padrao em torno do horario de saida, usada quando o horario da
     * linha nao define a sua. Ex.: saida 17h30 com 15/15 aceita 17h15-17h45.
     */
    'janela' => [
        'antes_minutos' => (int) env('PRESENCA_JANELA_ANTES_MIN', 15),
        'depois_minutos' => (int) env('PRESENCA_JANELA_DEPOIS_MIN', 15),
    ],

    'desafio' => [
        // Tempo para o navegador assinar e devolver o desafio. Curto, mas
        // suficiente para uma leitura de GPS lenta (ate 15 s no frontend).
        'validade_segundos' => (int) env('PRESENCA_DESAFIO_SEGUNDOS', 120),

        // Desafios usados ou vencidos sao apagados depois deste prazo.
        'retencao_horas' => (int) env('PRESENCA_DESAFIO_RETENCAO_HORAS', 24),
    ],

    'dispositivo' => [
        // A aprovacao do atendimento vale para uma tentativa concreta: o
        // estudante conclui o vinculo ali, nao dias depois (D07).
        'aprovacao_validade_minutos' => (int) env('PRESENCA_APROVACAO_MINUTOS', 30),
    ],

    'geocerca' => [
        // Estrategia de validacao da area (D01/D02). Hoje so ha "pontos":
        // pontos fixos de embarque com raio proprio. Regioes e corredor de
        // rota entram como novas implementacoes de PoliticaGeocerca.
        'politica' => env('PRESENCA_GEOCERCA_POLITICA', 'pontos'),

        // Leitura com precisao pior que isto pede nova tentativa ou revisao,
        // em vez de ampliar a tolerancia. Valor provisorio, a calibrar.
        'precisao_maxima_metros' => (int) env('PRESENCA_PRECISAO_MAXIMA_METROS', 100),

        // Teto do raio aceito no cadastro de um ponto. 1 km e a hipotese em
        // teste (D03), nao um padrao: cada ponto informa o seu raio.
        'raio_maximo_metros' => (int) env('PRESENCA_RAIO_MAXIMO_METROS', 1000),
    ],

    'retencao' => [
        // Dias de guarda das tentativas de presenca (D10). Vazio mantem tudo
        // ate a politica de retencao ser definida com o encarregado LGPD.
        // `filled` e nao `!== null`: "VAR=" no .env chega como texto vazio,
        // que viraria 0 dias e apagaria todas as tentativas.
        'tentativas_dias' => filled(env('PRESENCA_RETENCAO_TENTATIVAS_DIAS'))
            ? max(1, (int) env('PRESENCA_RETENCAO_TENTATIVAS_DIAS'))
            : null,
    ],

];
