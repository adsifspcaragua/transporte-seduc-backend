<?php

namespace Database\Factories;

use App\Models\Linha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Linha>
 */
class LinhaFactory extends Factory
{
    protected $model = Linha::class;

    public function definition(): array
    {
        return [
            'name' => 'Linha '.fake()->unique()->citySuffix(),
            'description' => fake()->sentence(),
            'departure_time' => '06:00:00',
            'return_time' => '22:30:00',
            'max_capacity' => 40,
        ];
    }
}
