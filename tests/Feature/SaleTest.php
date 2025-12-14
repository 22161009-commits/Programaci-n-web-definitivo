<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_registrar_una_venta_valida()
    {
        // Crear productos
        $product1 = Product::create([
            'codigo' => 'PROD-001',
            'nombre' => 'Producto 1',
            'precio' => 10.50,
            'existencias' => 50,
        ]);

        $product2 = Product::create([
            'codigo' => 'PROD-002',
            'nombre' => 'Producto 2',
            'precio' => 25.00,
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
        $response = $this->postJson(route('sales.store'), $saleData);

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
        $response = $this->postJson(route('sales.store'), $saleData);

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
            'codigo' => 'PROD-001',
            'nombre' => 'Producto con Stock Limitado',
            'precio' => 15.00,
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
        $response = $this->postJson(route('sales.store'), $saleData);

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
}

