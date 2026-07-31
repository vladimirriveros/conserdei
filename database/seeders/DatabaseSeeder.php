<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Sucursal;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\TipoCambio;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $faker = Faker::create();

        // Primero, llamar al seeder de Roles (necesario para asignar roles después)
        $this->call(RoleSeeder::class);

        // Crear admin protegido (DEBE ser el primero o al menos antes de asignar roles)
        $this->call(AdminUserSeeder::class);
        $this->call(GuestUserSeeder::class);

        // Resto de tus seeders...
        Proveedor::factory(5)->create();

        Sucursal::create([
            'nombre' => 'Sucursal CONSERDEI',
            'direccion' => 'Zona 12 de Octubre #123',
            'telefono' => '77712345',
            'activa' => true
        ]);

        $categorias = [
            ['nombre' => 'HERRAMIENTAS ELECTRICAS', 'descripcion' => 'Taladros, esmeriles, sierras'],
            ['nombre' => 'HERRAMIENTAS MANUALES', 'descripcion' => 'Martillos, destornilladores, llaves'],
            ['nombre' => 'MATERIAL ELECTRICO', 'descripcion' => 'Cables, interruptores, tomacorrientes'],
            ['nombre' => 'PLOMERIA', 'descripcion' => 'Tuberías, conexiones, grifería'],
            ['nombre' => 'PINTURAS', 'descripcion' => 'Pinturas, brochas, rodillos'],
            ['nombre' => 'FERRETERIA EN GENERAL', 'descripcion' => 'Clavos, tornillos, pernos'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create([
                'nombre' => $cat['nombre'],
                'descripcion' => $cat['descripcion'],
            ]);
        }

        TipoCambio::create(['id' => 1, 'precio_dolar' => 6.96, 'fecha' => now(), 'estado' => true, 'is_oficial' => true]);

        // Crear 5 productos
        for ($i = 1; $i <= 5; $i++) {
            $precioCompra = round(rand(100, 1000) / 10, 2);
            $porcentajeGanancia = round(rand(1, 100), 2);
            Producto::create([
                'categoria_id' => rand(1, 6),
                'codigo' => sprintf('%05d', $i),
                'nombre' => $faker->words(3, true),
                'marca' => $faker->company,
                'descripcion' => $faker->sentence,
                'imagen' => 'images/productos/conserdei.png', // Imagen por defecto local
                // 'imagen' => 'https://via.placeholder.com/640x480.png?text=' . $faker->word,
                'precio_compra' => $precioCompra,
                // 'precio_venta' => round(rand(100, 2000) / 10, 2),
                'porcentaje_ganancia' => $porcentajeGanancia,
                'precio_venta' => $precioCompra * (1 + $porcentajeGanancia / 100),
                'stock_minimo' => rand(1, 10),
                'stock_maximo' => 500,
                'unidad_medida' => 'g',
                'estado' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // En tu DatabaseSeeder o PermissionSeeder
        // $permission = Permission::create(['name' => 'compras.correccion']);

        // Crear usuarios de prueba (excepto el admin que ya se creó)
        // foreach (range(1, 2) as $index) {
        //     User::factory()->create([
        //         'password' => Hash::make('123456789'),
        //     ]);
        // }

        // Usuario de prueba admin
        $userAdmin = User::create([
            'name' => 'Luis Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('123456789')
        ]);
        $userAdmin->assignRole('admin');

        // Usuario de prueba vendedor
        $userVendedor = User::create([
            'name' => 'Vendedor',
            'email' => 'abc@abc.com',
            'password' => bcrypt('123456789')
        ]);
        $userVendedor->assignRole('vendedor');
    }
}
