<x-mail::message>
# Olá, {{ $estudante->name }}

Estamos escrevendo porque suas faltas no transporte universitário em **{{ $mes }}** estão perto do limite.

@if ($pertoSeguidas)
- Você está com **{{ $situacao['sequencia_atual'] }} faltas seguidas**. Com **{{ $situacao['limite_seguidas'] }} seguidas**, o benefício é suspenso.
@endif
@if ($pertoNoMes)
- Você tem **{{ $situacao['faltas_no_mes'] }} faltas** neste mês. Com **{{ $situacao['limite_no_mes'] }} faltas** no mês, seguidas ou não, o benefício é suspenso.
@endif

**Faltas registradas no mês:** {{ implode(', ', $datas) }}

A contagem recomeça no início de cada mês.

Se alguma dessas faltas teve motivo (atestado médico, prova, estágio etc.), informe o motorista ou a responsável pelo transporte para que a falta seja justificada. Faltas justificadas e aprovadas não contam para o limite.

Atenciosamente,<br>
Transporte Universitário — SEDUC Caraguatatuba
</x-mail::message>
