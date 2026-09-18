<x-mail::message>
# Olá, {{ $estudante->name }}

Seu benefício do transporte universitário foi **suspenso** por excesso de faltas em **{{ $mes }}**.

@if ($porSeguidas)
Você atingiu **{{ $situacao['maior_sequencia'] }} faltas seguidas** (o limite é {{ $situacao['limite_seguidas'] }}).
@else
Você atingiu **{{ $situacao['faltas_no_mes'] }} faltas** no mês (o limite é {{ $situacao['limite_no_mes'] }}).
@endif

**Faltas registradas no mês:** {{ implode(', ', $datas) }}

A partir de agora seu nome não aparece mais na lista de embarque. Para entender a situação ou pedir a revisão, procure a responsável pelo transporte universitário.

Atenciosamente,<br>
Transporte Universitário — SEDUC Caraguatatuba
</x-mail::message>
