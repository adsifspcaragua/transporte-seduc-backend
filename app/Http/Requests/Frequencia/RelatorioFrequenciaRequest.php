<?php

namespace App\Http\Requests\Frequencia;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class RelatorioFrequenciaRequest extends FormRequest
{
    /** Maior intervalo aceito: um ano letivo com folga. */
    private const MAXIMO_DIAS = 366;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'de' => 'nullable|date_format:Y-m-d',
            'ate' => 'nullable|date_format:Y-m-d|after_or_equal:de',
            'linha_id' => 'nullable|integer',
            'faltas_consecutivas_min' => 'nullable|integer|min:1',
        ];
    }

    /**
     * O relatorio le todas as marcacoes do intervalo de uma vez; sem teto, um
     * intervalo de anos traria o historico inteiro para a memoria.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty() || ! $this->filled('de')) {
                    return;
                }

                $de = Carbon::parse($this->input('de'));
                $ate = $this->filled('ate') ? Carbon::parse($this->input('ate')) : today();

                if ($de->diffInDays($ate) > self::MAXIMO_DIAS) {
                    $validator->errors()->add('de', 'O intervalo do relatório não pode passar de '.self::MAXIMO_DIAS.' dias.');
                }
            },
        ];
    }

    public function queryParameters(): array
    {
        return [
            'de' => ['description' => 'Inicio do periodo (AAAA-MM-DD). Padrao: 30 dias antes de `ate`.', 'example' => '2026-08-01'],
            'ate' => ['description' => 'Fim do periodo (AAAA-MM-DD). Padrao: hoje.', 'example' => '2026-08-31'],
            'linha_id' => ['description' => 'Restringe as chamadas de uma linha.', 'example' => 1],
            'faltas_consecutivas_min' => ['description' => 'Mostra so quem esta com ao menos este numero de faltas seguidas.', 'example' => 3],
        ];
    }
}
