<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@gesperes.bf'],
            [
                'name'     => 'Super Administrateur',
                'password' => Hash::make('GesPerES@2025!'),
                'actif'    => true,
            ]
        );

        $superAdmin->assignRole(RoleName::SUPER_ADMIN->value);

        $this->command->info("✓ Compte super-admin créé : admin@gesperes.bf / GesPerES@2025!");
        $this->command->warn("  ⚠  Changez ce mot de passe immédiatement après la première connexion !");
    }
}
