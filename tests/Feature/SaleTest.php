<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $this->admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function test_registrar_una_venta_valida()
    {
        // Crear productos
        $product1 = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto 1',
            'precio' => 10.50,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $product2 = Product::create([
            'codigo' => '002',
            'nombre' => 'Producto 2',
            'precio' => 25.00,
            'impuesto' => 16,
            'existencias' => 30,
        ]);

        // Datos de la venta
        $saleData = [
            'products' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2,
                    'price' => 10.50,
                    'subtotal' => 21.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1,
                    'price' => 25.00,
                    'subtotal' => 25.00,
                ],
            ],
            'total' => 46.00,
        ];

        // Enviar venta
        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), $saleData);

        // Verificar respuesta
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Venta registrada correctamente.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'sale_id',
            ]);

        $saleId = $response->json('sale_id');

        // Verificar registro en sales
        $this->assertDatabaseHas('sales', [
            'id' => $saleId,
            'total' => 46.00,
        ]);

        // Verificar registros en sale_details
        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $saleId,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 10.50,
            'subtotal' => 21.00,
        ]);

        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $saleId,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 25.00,
            'subtotal' => 25.00,
        ]);

        // Verificar descuento de stock
        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(48, $product1->existencias); // 50 - 2 = 48
        $this->assertEquals(29, $product2->existencias); // 30 - 1 = 29
    }

    /** @test */
    public function test_no_permitir_venta_sin_productos()
    {
        // Datos de venta sin productos
        $saleData = [
            'products' => [],
            'total' => 0,
        ];

        // Enviar venta
        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), $saleData);

        // Debe fallar con 422
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);

        // Verificar que no se creó ninguna venta
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_details', 0);
    }

    /** @test */
    public function test_no_permitir_venta_con_stock_insuficiente()
    {
        // Crear producto con stock limitado
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto con Stock Limitado',
            'precio' => 15.00,
            'impuesto' => 16,
            'existencias' => 5, // Solo hay 5 unidades
        ]);

        $stockInicial = $product->existencias;

        // Datos de venta con cantidad mayor al stock disponible
        $saleData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10, // Intentar comprar 10, pero solo hay 5
                    'price' => 15.00,
                    'subtotal' => 150.00,
                ],
            ],
            'total' => 150.00,
        ];

        // Enviar venta
        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), $saleData);

        // Debe fallar
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'message' => "Stock insuficiente para el producto {$product->nombre}. Disponible: 5, Solicitado: 10"
            ]);

        // Verificar que NO se creó ninguna venta
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_details', 0);

        // Verificar que NO se descontó inventario
        $product->refresh();
        $this->assertEquals($stockInicial, $product->existencias); // Debe seguir siendo 5
    }

    /** @test */
    public function test_listar_ventas()
    {
        $sale1 = Sale::create([
            'total' => 100.00,
            'user_id' => $this->vendedor->id,
        ]);

        $sale2 = Sale::create([
            'total' => 200.00,
            'user_id' => $this->vendedor->id,
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.index'));

        $response->assertStatus(200);
        $response->assertViewIs('sales.index');
        $response->assertViewHas('sales');
    }

    /** @test */
    public function test_listar_ventas_con_filtro_por_fecha()
    {
        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $this->vendedor->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.index', [
            'fecha' => now()->subDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /** @test */
    public function test_listar_ventas_con_filtro_por_vendedor()
    {
        $vendedor2 = User::create([
            'name' => 'Vendedor 2',
            'username' => 'vendedor2',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $sale1 = Sale::create([
            'total' => 100.00,
            'user_id' => $this->vendedor->id,
        ]);

        $sale2 = Sale::create([
            'total' => 200.00,
            'user_id' => $vendedor2->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('sales.index', [
            'vendedor_id' => $this->vendedor->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('sales');
    }

    /** @test */
    public function test_vendedor_solo_ve_sus_propias_ventas()
    {
        $vendedor2 = User::create([
            'name' => 'Vendedor 2',
            'username' => 'vendedor2',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $sale1 = Sale::create([
            'total' => 100.00,
            'user_id' => $this->vendedor->id,
        ]);

        $sale2 = Sale::create([
            'total' => 200.00,
            'user_id' => $vendedor2->id,
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.index'));

        $response->assertStatus(200);
        $sales = $response->viewData('sales');
        $this->assertCount(1, $sales);
        $this->assertEquals($sale1->id, $sales->first()->id);
    }

    /** @test */
    public function test_mostrar_pantalla_pos()
    {
        $response = $this->actingAs($this->vendedor)->get(route('sales.pos'));

        $response->assertStatus(200);
        $response->assertViewIs('sales.pos');
    }

    /** @test */
    public function test_buscar_productos_por_nombre()
    {
        Product::create([
            'codigo' => '001',
            'nombre' => 'Producto Test',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.search', ['q' => 'Test']));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /** @test */
    public function test_buscar_productos_por_codigo()
    {
        Product::create([
            'codigo' => '001',
            'nombre' => 'Producto Test',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.search', ['q' => '001']));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    /** @test */
    public function test_buscar_productos_retorna_vacio_si_query_vacio()
    {
        $response = $this->actingAs($this->vendedor)->get(route('sales.search', ['q' => '']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /** @test */
    public function test_mostrar_detalle_de_venta()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $sale = Sale::create([
            'total' => 10.00,
            'user_id' => $this->vendedor->id,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
            'subtotal' => 10.00,
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.show', $sale));

        $response->assertStatus(200);
        $response->assertViewIs('sales.show');
        $response->assertViewHas('sale');
    }

    /** @test */
    public function test_mostrar_ticket_de_venta()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $sale = Sale::create([
            'total' => 10.00,
            'user_id' => $this->vendedor->id,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 10.00,
            'subtotal' => 10.00,
        ]);

        $response = $this->actingAs($this->vendedor)->get(route('sales.ticket', $sale));

        $response->assertStatus(200);
        $response->assertViewIs('sales.ticket');
        $response->assertViewHas('sale');
    }

    /** @test */
    public function test_cancelar_venta_exitosamente()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $sale = Sale::create([
            'total' => 10.00,
            'user_id' => $this->vendedor->id,
        ]);

        SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price' => 10.00,
            'subtotal' => 50.00,
        ]);

        $stockInicial = $product->existencias;
        $product->existencias -= 5;
        $product->save();

        $response = $this->actingAs($this->admin)->post(route('sales.cancel', $sale), [
            'admin_username' => 'admin',
            'admin_password' => 'password',
        ]);

        $response->assertRedirect(route('sales.index'));
        $response->assertSessionHas('success');

        $sale->refresh();
        $this->assertTrue($sale->cancelada);
        $this->assertNotNull($sale->cancelada_at);
        $this->assertEquals($this->admin->id, $sale->cancelada_por);

        $product->refresh();
        $this->assertEquals($stockInicial, $product->existencias);
    }

    /** @test */
    public function test_no_cancelar_venta_ya_cancelada()
    {
        $sale = Sale::create([
            'total' => 10.00,
            'user_id' => $this->vendedor->id,
            'cancelada' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.cancel', $sale), [
            'admin_username' => 'admin',
            'admin_password' => 'password',
        ]);

        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_no_cancelar_venta_con_credenciales_incorrectas()
    {
        $sale = Sale::create([
            'total' => 10.00,
            'user_id' => $this->vendedor->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('sales.cancel', $sale), [
            'admin_username' => 'admin',
            'admin_password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('admin_credentials');
        $sale->refresh();
        $this->assertFalse($sale->cancelada);
    }

    /** @test */
    public function test_venta_con_total_impuestos()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $saleData = [
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 10.00,
                    'subtotal' => 10.00,
                ],
            ],
            'total' => 10.00,
            'total_impuestos' => 1.60,
        ];

        $response = $this->actingAs($this->vendedor)->postJson(route('sales.store'), $saleData);

        $response->assertStatus(201);
        $saleId = $response->json('sale_id');
        $this->assertDatabaseHas('sales', [
            'id' => $saleId,
            'total_impuestos' => 1.60,
        ]);
    }

    /** @test */
    public function test_requiere_autenticacion_para_acceder_a_ventas()
    {
        $response = $this->get(route('sales.index'));

        $response->assertRedirect(route('login'));
    }
}

