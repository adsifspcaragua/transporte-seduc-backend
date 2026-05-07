# Autenticacao das requisicoes

Para autenticar as requisicoes, inclua o cabecalho **`Authorization`** com o valor **`"Bearer TOKEN_DE_ACESSO"`**.

Todos os endpoints autenticados aparecem marcados com o selo `requer autenticacao` na documentacao abaixo.

Use <code>POST /api/auth/token</code> informando e-mail ou CPF e senha para gerar um token Bearer.
