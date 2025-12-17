<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_redirigir_a_login_si_no_esta_autenticado()
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_permitir_acceso_a_admin()
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_denegar_acceso_a_vendedor()
    {
        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $response = $this->actingAs($vendedor)->get(route('products.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_permitir_acceso_a_vendedor_a_rutas_de_ventas()
    {
        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $response = $this->actingAs($vendedor)->get(route('sales.index'));

        $response->assertStatus(200);
    }
}

