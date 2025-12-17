<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Provider;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);
    }

    // Tests para LoginRequest
    /** @test */
    public function test_login_requiere_username()
    {
        $response = $this->post(route('login'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }

    /** @test */
    public function test_login_requiere_password()
    {
        $response = $this->post(route('login'), [
            'username' => 'test',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // Tests para RegisterRequest
    /** @test */
    public function test_registro_requiere_nombre()
    {
        $response = $this->post(route('register'), [
            'username' => 'test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_registro_requiere_username_unico()
    {
        User::create([
            'name' => 'Test',
            'username' => 'test',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $response = $this->post(route('register'), [
            'name' => 'Test 2',
            'username' => 'test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }

    /** @test */
    public function test_registro_requiere_password_confirmado()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test',
            'username' => 'test',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function test_registro_username_solo_letras_numeros_guiones()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test',
            'username' => 'test@user',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
    }

    // Tests para RegisterAdminRequest
    /** @test */
    public function test_registro_admin_requiere_admin_password()
    {
        $response = $this->post(route('register.admin.store'), [
            'name' => 'Admin',
            'username' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('admin_password');
    }

    // Tests para StoreProductRequest
    /** @test */
    public function test_producto_requiere_codigo()
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response->assertSessionHasErrors('codigo');
    }

    /** @test */
    public function test_producto_codigo_solo_numeros()
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'codigo' => 'ABC-001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response->assertSessionHasErrors('codigo');
    }

    /** @test */
    public function test_producto_requiere_precio_mayor_a_cero()
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 0,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response->assertSessionHasErrors('precio');
    }

    /** @test */
    public function test_producto_impuesto_entre_0_y_100()
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 101,
            'existencias' => 50,
        ]);

        $response->assertSessionHasErrors('impuesto');
    }

    /** @test */
    public function test_producto_proveedor_debe_existir()
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
            'providers' => [999],
        ]);

        $response->assertSessionHasErrors('providers.0');
    }

    // Tests para StoreProviderRequest
    /** @test */
    public function test_proveedor_requiere_nombre()
    {
        $response = $this->actingAs($this->admin)->post(route('providers.store'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_proveedor_email_debe_ser_valido()
    {
        $response = $this->actingAs($this->admin)->post(route('providers.store'), [
            'name' => 'Proveedor',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // Tests para StoreSaleRequest
    /** @test */
    public function test_venta_requiere_productos()
    {
        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), [
            'total' => 100.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products']);
    }

    /** @test */
    public function test_venta_requiere_al_menos_un_producto()
    {
        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), [
            'products' => [],
            'total' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products']);
    }

    /** @test */
    public function test_venta_producto_debe_existir()
    {
        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), [
            'products' => [
                [
                    'product_id' => 999,
                    'quantity' => 1,
                    'price' => 10.00,
                    'subtotal' => 10.00,
                ],
            ],
            'total' => 10.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products.0.product_id']);
    }

    /** @test */
    public function test_venta_cantidad_debe_ser_mayor_a_cero()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'price' => 10.00,
                    'subtotal' => 0,
                ],
            ],
            'total' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products.0.quantity']);
    }

    /** @test */
    public function test_venta_precio_no_puede_ser_negativo()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => -10.00,
                    'subtotal' => -10.00,
                ],
            ],
            'total' => -10.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products.0.price']);
    }

    // Tests para CancelSaleRequest
    /** @test */
    public function test_cancelar_venta_requiere_admin_username()
    {
        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $this->vendedor->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.cancel', $sale), [
            'admin_password' => 'password',
        ]);

        $response->assertSessionHasErrors('admin_username');
    }

    /** @test */
    public function test_cancelar_venta_requiere_admin_password()
    {
        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $this->vendedor->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.cancel', $sale), [
            'admin_username' => 'admin',
        ]);

        $response->assertSessionHasErrors('admin_password');
    }
}

