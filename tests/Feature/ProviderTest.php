<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function test_listar_proveedores()
    {
        Provider::create(['name' => 'Proveedor 1']);
        Provider::create(['name' => 'Proveedor 2']);

        $response = $this->actingAs($this->admin)->get(route('providers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('providers.index');
        $response->assertViewHas('providers');
    }

    /** @test */
    public function test_mostrar_formulario_de_creacion()
    {
        $response = $this->actingAs($this->admin)->get(route('providers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('providers.create');
    }

    /** @test */
    public function test_crear_proveedor_correctamente()
    {
        $providerData = [
            'name' => 'Proveedor de Prueba',
            'contact_name' => 'Juan Pérez',
            'phone' => '555-1234',
            'email' => 'proveedor@example.com',
            'address' => 'Calle Principal 123',
        ];

        $response = $this->actingAs($this->admin)->post(route('providers.store'), $providerData);

        $response->assertRedirect(route('providers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('providers', [
            'name' => 'Proveedor de Prueba',
            'contact_name' => 'Juan Pérez',
            'phone' => '555-1234',
            'email' => 'proveedor@example.com',
            'address' => 'Calle Principal 123',
        ]);
    }

    /** @test */
    public function test_no_permitir_crear_proveedor_sin_nombre()
    {
        $providerData = [
            'name' => '',
            'email' => 'proveedor@example.com',
        ];

        $response = $this->actingAs($this->admin)->post(route('providers.store'), $providerData);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('providers', 0);
    }

    /** @test */
    public function test_no_permitir_nombres_de_proveedor_duplicados()
    {
        Provider::create([
            'name' => 'Proveedor Existente',
            'email' => 'existente@example.com',
        ]);

        $providerData = [
            'name' => 'Proveedor Existente',
            'email' => 'nuevo@example.com',
        ];

        $response = $this->actingAs($this->admin)->post(route('providers.store'), $providerData);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('providers', 1);
    }

    /** @test */
    public function test_no_permitir_email_invalido()
    {
        $providerData = [
            'name' => 'Proveedor con Email Inválido',
            'email' => 'email-invalido',
        ];

        $response = $this->actingAs($this->admin)->post(route('providers.store'), $providerData);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('providers', 0);
    }

    /** @test */
    public function test_mostrar_formulario_de_edicion()
    {
        $provider = Provider::create([
            'name' => 'Proveedor',
        ]);

        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $provider->products()->attach($product->id);

        $response = $this->actingAs($this->admin)->get(route('providers.edit', $provider));

        $response->assertStatus(200);
        $response->assertViewIs('providers.edit');
        $response->assertViewHas('provider');
        $response->assertViewHas('products');
    }

    /** @test */
    public function test_actualizar_proveedor_correctamente()
    {
        $provider = Provider::create([
            'name' => 'Proveedor Original',
            'contact_name' => 'Contacto Original',
            'phone' => '555-0000',
            'email' => 'original@example.com',
            'address' => 'Dirección Original',
        ]);

        $updateData = [
            'name' => 'Proveedor Actualizado',
            'contact_name' => 'Contacto Actualizado',
            'phone' => '555-9999',
            'email' => 'actualizado@example.com',
            'address' => 'Dirección Actualizada',
        ];

        $response = $this->actingAs($this->admin)->put(route('providers.update', $provider), $updateData);

        $response->assertRedirect(route('providers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'name' => 'Proveedor Actualizado',
            'contact_name' => 'Contacto Actualizado',
            'phone' => '555-9999',
            'email' => 'actualizado@example.com',
            'address' => 'Dirección Actualizada',
        ]);
    }

    /** @test */
    public function test_actualizar_proveedor_con_productos()
    {
        $provider = Provider::create(['name' => 'Proveedor']);

        $product1 = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto 1',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $product2 = Product::create([
            'codigo' => '002',
            'nombre' => 'Producto 2',
            'precio' => 20.00,
            'impuesto' => 16,
            'existencias' => 30,
        ]);

        $updateData = [
            'name' => 'Proveedor',
            'products' => [$product1->id, $product2->id],
        ];

        $response = $this->actingAs($this->admin)->put(route('providers.update', $provider), $updateData);

        $response->assertRedirect(route('providers.index'));
        $provider->refresh();
        $this->assertCount(2, $provider->products);
    }

    /** @test */
    public function test_eliminar_proveedor()
    {
        $provider = Provider::create([
            'name' => 'Proveedor a Eliminar',
            'email' => 'eliminar@example.com',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('providers.destroy', $provider));

        $response->assertRedirect(route('providers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('providers', [
            'id' => $provider->id,
        ]);
    }

    /** @test */
    public function test_permitir_actualizar_proveedor_con_mismo_nombre()
    {
        $provider = Provider::create([
            'name' => 'Proveedor Original',
            'email' => 'original@example.com',
        ]);

        $updateData = [
            'name' => 'Proveedor Original', // Mismo nombre
            'email' => 'nuevo@example.com',
        ];

        $response = $this->actingAs($this->admin)->put(route('providers.update', $provider), $updateData);

        $response->assertRedirect(route('providers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'name' => 'Proveedor Original',
            'email' => 'nuevo@example.com',
        ]);
    }

    /** @test */
    public function test_campos_opcionales_pueden_ser_nulos()
    {
        $providerData = [
            'name' => 'Proveedor Mínimo',
            // No se envían contact_name, phone, email, address
        ];

        $response = $this->actingAs($this->admin)->post(route('providers.store'), $providerData);

        $response->assertRedirect(route('providers.index'));
        $this->assertDatabaseHas('providers', [
            'name' => 'Proveedor Mínimo',
            'contact_name' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
        ]);
    }

    /** @test */
    public function test_requiere_autenticacion_para_acceder_a_proveedores()
    {
        $response = $this->get(route('providers.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_requiere_rol_admin_para_acceder_a_proveedores()
    {
        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $response = $this->actingAs($vendedor)->get(route('providers.index'));

        $response->assertStatus(403);
    }
}

