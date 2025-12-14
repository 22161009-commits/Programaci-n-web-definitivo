<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_crear_producto_con_datos_validos()
    {
        $productData = [
            'codigo' => 'PROD-001',
            'nombre' => 'Producto de Prueba',
            'precio' => 99.99,
            'existencias' => 50,
        ];

        $response = $this->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'codigo' => 'PROD-001',
            'nombre' => 'Producto de Prueba',
            'precio' => 99.99,
            'existencias' => 50,
        ]);
    }

    /** @test */
    public function test_no_se_puede_crear_producto_con_codigo_duplicado()
    {
        Product::create([
            'codigo' => 'PROD-001',
            'nombre' => 'Producto Existente',
            'precio' => 50.00,
            'existencias' => 10,
        ]);

        $productData = [
            'codigo' => 'PROD-001',
            'nombre' => 'Producto Duplicado',
            'precio' => 75.00,
            'existencias' => 20,
        ];

        $response = $this->post(route('products.store'), $productData);

        $response->assertSessionHasErrors('codigo');
        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function test_eliminar_producto()
    {
        $product = Product::create([
            'codigo' => 'PROD-001',
            'nombre' => 'Producto a Eliminar',
            'precio' => 100.00,
            'existencias' => 30,
        ]);

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
