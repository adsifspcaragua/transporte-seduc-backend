<?php

use App\Models\InscricaoInstituicoes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * O transporte só vai para São José dos Campos. Os valores gravados antes
     * vinham de uma lista de cidades de origem (Caraguatatuba, Ubatuba...)
     * oferecida por engano no formulário.
     */
    public function up(): void
    {
        DB::table('inscricao_instituicoes')
            ->whereNotNull('city_destination')
            ->where('city_destination', '!=', InscricaoInstituicoes::CIDADE_DESTINO)
            ->update(['city_destination' => InscricaoInstituicoes::CIDADE_DESTINO]);
    }

    public function down(): void
    {
        // Correção de dados: os valores anteriores eram inválidos e não são restaurados.
    }
};
