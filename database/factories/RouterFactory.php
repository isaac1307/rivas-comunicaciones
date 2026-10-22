<?php

namespace Database\Factories;

use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Router>
 */
class RouterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Nodo '.$this->faker->city(),
            'ip_address' => $this->faker->ipv4(),
            'api_user' => 'admin',
            'api_password' => 'secret_password_123',
            'estado' => 'activo',
        ];
    }
}
