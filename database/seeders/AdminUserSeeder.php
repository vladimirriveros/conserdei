<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar o crear el admin protegido
        $admin = User::withTrashed()->updateOrCreate(
            ['email' => 'vlavlavlariver@gmail.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('@dmin123'),
                'email_verified_at' => now(),
                'is_protected' => true, // 🔒 Esto lo hace indeletable
            ]
        );

        // Asignar rol usando Spatie
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Si estaba eliminado, restaurarlo
        if ($admin->trashed()) {
            $admin->restore();
        }

        $this->command->info('✅ Administrador protegido creado/actualizado correctamente.');
    }
}
