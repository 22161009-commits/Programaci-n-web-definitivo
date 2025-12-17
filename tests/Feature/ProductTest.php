<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductTest extends TestCase
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
    public function test_listar_productos()
    {
        Product::create([
            'codigo' => '001',
            'nombre' => 'Producto 1',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        Product::create([
            'codigo' => '002',
            'nombre' => 'Producto 2',
            'precio' => 20.00,
            'impuesto' => 16,
            'existencias' => 30,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
    }

    /** @test */
    public function test_mostrar_formulario_de_creacion()
    {
        Provider::create([
            'name' => 'Proveedor 1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
        $response->assertViewHas('providers');
    }

    /** @test */
    public function test_crear_producto_con_datos_validos()
    {
        $productData = [
            'codigo' => '001',
            'nombre' => 'Producto de Prueba',
            'precio' => 99.99,
            'impuesto' => 16,
            'existencias' => 50,
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'codigo' => '001',
            'nombre' => 'Producto de Prueba',
            'precio' => 99.99,
            'existencias' => 50,
        ]);
    }

    /** @test */
    public function test_crear_producto_con_proveedores()
    {
        $provider1 = Provider::create(['name' => 'Proveedor 1']);
        $provider2 = Provider::create(['name' => 'Proveedor 2']);

        $productData = [
            'codigo' => '001',
            'nombre' => 'Producto con Proveedores',
            'precio' => 99.99,
            'impuesto' => 16,
            'existencias' => 50,
            'providers' => [$provider1->id, $provider2->id],
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'));
        $product = Product::where('codigo', '001')->first();
        $this->assertCount(2, $product->providers);
    }

    /** @test */
    public function test_no_se_puede_crear_producto_con_codigo_duplicado()
    {
        Product::create([
            'codigo' => '001',
            'nombre' => 'Producto Existente',
            'precio' => 50.00,
            'impuesto' => 16,
            'existencias' => 10,
        ]);

        $productData = [
            'codigo' => '001',
            'nombre' => 'Producto Duplicado',
            'precio' => 75.00,
            'impuesto' => 16,
            'existencias' => 20,
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertSessionHasErrors('codigo');
        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function test_no_se_puede_crear_producto_con_nombre_duplicado()
    {
        Product::create([
            'codigo' => '001',
            'nombre' => 'Producto Existente',
            'precio' => 50.00,
            'impuesto' => 16,
            'existencias' => 10,
        ]);

        $productData = [
            'codigo' => '002',
            'nombre' => 'Producto Existente',
            'precio' => 75.00,
            'impuesto' => 16,
            'existencias' => 20,
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertSessionHasErrors('nombre');
        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function test_validacion_de_codigo_solo_numeros()
    {
        $productData = [
            'codigo' => 'ABC-001',
            'nombre' => 'Producto',
            'precio' => 99.99,
            'impuesto' => 16,
            'existencias' => 50,
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertSessionHasErrors('codigo');
    }

    /** @test */
    public function test_mostrar_formulario_de_edicion()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $provider = Provider::create(['name' => 'Proveedor 1']);
        $product->providers()->attach($provider->id);

        $response = $this->actingAs($this->admin)->get(route('products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('products.edit');
        $response->assertViewHas('product');
        $response->assertViewHas('providers');
    }

    /** @test */
    public function test_actualizar_producto()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto Original',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $updateData = [
            'codigo' => '002',
            'nombre' => 'Producto Actualizado',
            'precio' => 20.00,
            'impuesto' => 18,
            'existencias' => 100,
        ];

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), $updateData);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'codigo' => '002',
            'nombre' => 'Producto Actualizado',
            'precio' => 20.00,
            'existencias' => 100,
        ]);
    }

    /** @test */
    public function test_actualizar_producto_con_proveedores()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $provider1 = Provider::create(['name' => 'Proveedor 1']);
        $provider2 = Provider::create(['name' => 'Proveedor 2']);

        $updateData = [
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
            'providers' => [$provider1->id, $provider2->id],
        ];

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), $updateData);

        $response->assertRedirect(route('products.index'));
        $product->refresh();
        $this->assertCount(2, $product->providers);
    }

    /** @test */
    public function test_eliminar_producto()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto a Eliminar',
            'precio' => 100.00,
            'impuesto' => 16,
            'existencias' => 30,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function test_calculo_de_impuesto_al_crear()
    {
        $productData = [
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 116.00, // Precio con IVA incluido
            'impuesto' => 16,
            'existencias' => 50,
        ];

        $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $product = Product::where('codigo', '001')->first();
        $this->assertNotNull($product->impuesto_calculado);
        $this->assertGreaterThan(0, $product->impuesto_calculado);
    }

    /** @test */
    public function test_requiere_autenticacion_para_acceder_a_productos()
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_requiere_rol_admin_para_acceder_a_productos()
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
}
