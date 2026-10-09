<x-mail::message>
# Olá, {{ $estudante->name }}

Seu recadastro no transporte universitário foi analisado pela equipe responsável.

**Situação do recadastro:** {{ $status }}

@if ($camposPendentes !== [])
**Campos que precisam de correção:** {{ implode(', ', $camposPendentes) }}
@endif

@if (filled($solicitacao->observacoes))
**Observação:** {{ $solicitacao->observacoes }}
@endif

Este é um e-mail automático. Não responda a esta mensagem.

Atenciosamente,<br>
Transporte Universitário — SEDUC Caraguatatuba
</x-mail::message>
