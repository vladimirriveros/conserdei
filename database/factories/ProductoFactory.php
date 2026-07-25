<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $precio_compra = $this->faker->randomFloat(2, 10, 100);
        $precio_venta = $this->faker->randomFloat(2, 15, 150);

        // Calcula el porcentaje de ganancia
        $porcentaje_ganancia = ($precio_venta - $precio_compra) / $precio_compra * 100;

        return [
            'categoria_id' => rand(1,10),
            // 'codigo' => $this->faker->unique()->numerify('PROD-#####'),
            'codigo' => $this->faker->unique()->numerify('#####'),
            'nombre' => $this->faker->word(),
            'marca' => $this->faker->word(),
            'descripcion' => $this->faker->sentence(),
            'imagen' => $this->faker->imageUrl(640, 480, 'products'),
            // 'precio_compra' => $this->faker->randomFloat(2, 10, 100),
            // 'precio_venta' => $this->faker->randomFloat(2, 15, 150),
            'precio_compra' => $precio_compra,
            'precio_venta' => $precio_venta,
            'porcentaje_ganancia' => round($porcentaje_ganancia, 2),
            'stock_minimo' => $this->faker->numberBetween(1, 5),
            'stock_maximo' => $this->faker->numberBetween(10, 20),
            'unidad_medida' => $this->faker->randomElement(['unidad', 'kg', 'g', 'l', 'ml']),
            'estado' => $this->faker->randomElement(['1', '0']),
        ];
    }
}
