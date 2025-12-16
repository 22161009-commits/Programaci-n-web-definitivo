<?php

namespace Tests\Feature;

use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->post(route('providers.store'), $providerData);

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

        $response = $this->post(route('providers.store'), $providerData);

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

        $response = $this->post(route('providers.store'), $providerData);

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

        $response = $this->post(route('providers.store'), $providerData);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('providers', 0);
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

        $response = $this->put(route('providers.update', $provider), $updateData);

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
    public function test_eliminar_proveedor()
    {
        $provider = Provider::create([
            'name' => 'Proveedor a Eliminar',
            'email' => 'eliminar@example.com',
        ]);

        $response = $this->delete(route('providers.destroy', $provider));

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

        $response = $this->put(route('providers.update', $provider), $updateData);

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

        $response = $this->post(route('providers.store'), $providerData);

        $response->assertRedirect(route('providers.index'));
        $this->assertDatabaseHas('providers', [
            'name' => 'Proveedor Mínimo',
            'contact_name' => null,
            'phone' => null,
            'email' => null,
            'address' => null,
        ]);
    }
}

