<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Provider;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_producto_tiene_proveedores()
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

        $product->providers()->attach([$provider1->id, $provider2->id]);

        $this->assertCount(2, $product->providers);
        $this->assertTrue($product->providers->contains($provider1));
        $this->assertTrue($product->providers->contains($provider2));
    }

    /** @test */
    public function test_proveedor_tiene_productos()
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

        $provider->products()->attach([$product1->id, $product2->id]);

        $this->assertCount(2, $provider->products);
        $this->assertTrue($provider->products->contains($product1));
        $this->assertTrue($provider->products->contains($product2));
    }

    /** @test */
    public function test_venta_tiene_detalles()
    {
        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $vendedor->id,
        ]);

        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $detail1 = SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10.00,
            'subtotal' => 20.00,
        ]);

        $detail2 = SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 10.00,
            'subtotal' => 30.00,
        ]);

        $this->assertCount(2, $sale->saleDetails);
        $this->assertTrue($sale->saleDetails->contains($detail1));
        $this->assertTrue($sale->saleDetails->contains($detail2));
    }

    /** @test */
    public function test_venta_pertenece_a_usuario()
    {
        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $vendedor->id,
        ]);

        $this->assertEquals($vendedor->id, $sale->user->id);
        $this->assertEquals($vendedor->name, $sale->user->name);
    }

    /** @test */
    public function test_venta_tiene_admin_que_cancelo()
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $vendedor->id,
            'cancelada' => true,
            'cancelada_por' => $admin->id,
        ]);

        $this->assertEquals($admin->id, $sale->cancelledBy->id);
        $this->assertEquals($admin->name, $sale->cancelledBy->name);
    }

    /** @test */
    public function test_detalle_pertenece_a_venta()
    {
        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $vendedor->id,
        ]);

        $detail = SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10.00,
            'subtotal' => 20.00,
        ]);

        $this->assertEquals($sale->id, $detail->sale->id);
    }

    /** @test */
    public function test_detalle_pertenece_a_producto()
    {
        $product = Product::create([
            'codigo' => '001',
            'nombre' => 'Producto',
            'precio' => 10.00,
            'impuesto' => 16,
            'existencias' => 50,
        ]);

        $vendedor = User::create([
            'name' => 'Vendedor',
            'username' => 'vendedor',
            'password' => Hash::make('password'),
            'role' => 'vendedor',
        ]);

        $sale = Sale::create([
            'total' => 100.00,
            'user_id' => $vendedor->id,
        ]);

        $detail = SaleDetail::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 10.00,
            'subtotal' => 20.00,
        ]);

        $this->assertEquals($product->id, $detail->product->id);
        $this->assertEquals($product->nombre, $detail->product->nombre);
    }
}

