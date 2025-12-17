<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_mostrar_formulario_de_login()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function test_redirigir_al_dashboard_si_ya_esta_autenticado_al_ver_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function test_login_exitoso_con_credenciales_validas()
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'role' => 'vendedor',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function test_login_con_remember_me()
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'role' => 'vendedor',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function test_login_falla_con_credenciales_invalidas()
    {
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password123'),
            'role' => 'vendedor',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function test_login_falla_con_usuario_inexistente()
    {
        $response = $this->post(route('login'), [
            'username' => 'nonexistent',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function test_mostrar_formulario_de_registro()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function test_redirigir_al_dashboard_si_ya_esta_autenticado_al_ver_registro()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function test_registro_exitoso_de_vendedor()
    {
        $userData = [
            'name' => 'Nuevo Vendedor',
            'username' => 'vendedor1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Nuevo Vendedor',
            'username' => 'vendedor1',
            'role' => 'vendedor',
        ]);
        $this->assertAuthenticated();
    }

    /** @test */
    public function test_registro_falla_sin_confirmacion_de_password()
    {
        $userData = [
            'name' => 'Nuevo Vendedor',
            'username' => 'vendedor1',
            'password' => 'password123',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    /** @test */
    public function test_mostrar_formulario_de_registro_admin()
    {
        $response = $this->get(route('register.admin'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register-admin');
    }

    /** @test */
    public function test_redirigir_al_dashboard_si_ya_esta_autenticado_al_ver_registro_admin()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register.admin'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function test_registro_exitoso_de_admin_con_contraseña_maestra_correcta()
    {
        $userData = [
            'name' => 'Nuevo Admin',
            'username' => 'admin1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admin_password' => 'admin2024',
        ];

        $response = $this->post(route('register.admin.store'), $userData);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Nuevo Admin',
            'username' => 'admin1',
            'role' => 'admin',
        ]);
        $this->assertAuthenticated();
    }

    /** @test */
    public function test_registro_admin_falla_con_contraseña_maestra_incorrecta()
    {
        $userData = [
            'name' => 'Nuevo Admin',
            'username' => 'admin1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'admin_password' => 'wrongpassword',
        ];

        $response = $this->post(route('register.admin.store'), $userData);

        $response->assertSessionHasErrors('admin_password');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    /** @test */
    public function test_logout_exitoso()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();
    }

    /** @test */
    public function test_dashboard_muestra_vista_admin_para_admin()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin');
    }

    /** @test */
    public function test_dashboard_muestra_vista_vendedor_para_vendedor()
    {
        $vendedor = User::create([
            'name' => 'Vendedor User',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $response = $this->actingAs($vendedor)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.vendedor');
    }

    /** @test */
    public function test_redirigir_a_login_si_no_esta_autenticado_al_acceder_al_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}

