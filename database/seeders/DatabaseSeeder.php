<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'employee_number' => 'E000001',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Crear usuario vendedor de ejemplo
        User::create([
            'name' => 'Vendedor',
            'employee_number' => 'E000002',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);
    }
}
