<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    public function run(): void
    {
        $guest = User::withTrashed()->updateOrCreate(
            ['email' => 'invitado@conserdei.demo'],
            [
                'name' => 'Usuario Invitado',
                'password' => Hash::make('Invitado123!'),
                'email_verified_at' => now(),
                'is_protected' => true,
            ]
        );

        if ($guest->trashed()) {
            $guest->restore();
        }

        $guest->syncRoles(['invitado']);

        $this->command?->info('Usuario invitado de solo lectura creado correctamente.');
    }
}
