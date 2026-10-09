<x-mail::message>
# Olá, {{ $inscricao->name }}

Sua inscrição no transporte universitário foi analisada pela equipe responsável.

**Situação da inscrição:** {{ $status }}

@if ($camposPendentes !== [])
**Campos que precisam de correção:** {{ implode(', ', $camposPendentes) }}
@endif

@if (filled($inscricao->observation))
**Observação:** {{ $inscricao->observation }}
@endif

Este é um e-mail automático. Não responda a esta mensagem.

Atenciosamente,<br>
Transporte Universitário — SEDUC Caraguatatuba
</x-mail::message>
