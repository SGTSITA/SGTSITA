<?php

namespace Database\Factories;

use App\Models\Especialist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EspecialistFactory extends Factory
{
    protected $model = Especialist::class;

    public function definition()
    {
        return [
			'nombre' => $this->faker->name,
			'apellido' => $this->faker->name,
			'cedula' => $this->faker->name,
			'especialidad' => $this->faker->name,
			'telefono' => $this->faker->name,
			'fecha_nacimiento' => $this->faker->name,
			'email' => $this->faker->name,
        ];
    }
}
