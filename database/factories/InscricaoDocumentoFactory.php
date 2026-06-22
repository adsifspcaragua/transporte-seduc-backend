<?php

namespace Database\Factories;

use App\Models\InscricaoDocumento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InscricaoDocumento>
 */
class InscricaoDocumentoFactory extends Factory
{
    protected $model = InscricaoDocumento::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'foto',
            'type' => 'image/png',
            'file_path' => 'documentos/foto.png',
            'status' => 'Em analise',
        ];
    }
}
