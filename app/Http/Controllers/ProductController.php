<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $providers = Provider::all();
        return view('products.create', compact('providers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();
        
        // Calcular el impuesto: precio * (impuesto / 100) / (1 + impuesto / 100)
        // Si el precio ya incluye IVA, extraemos el impuesto
        $precio = $validated['precio'];
        $impuestoPorcentaje = $validated['impuesto'];
        $impuestoCalculado = $precio * ($impuestoPorcentaje / 100) / (1 + ($impuestoPorcentaje / 100));
        
        $validated['impuesto_calculado'] = round($impuestoCalculado, 2);
        
        $product = Product::create($validated);

        // Sincronizar proveedores asociados (filtrar valores vacíos)
        $providers = array_filter($request->input('providers', []), function($value) {
            return !empty($value);
        });
        $product->providers()->sync($providers);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $providers = Provider::all();
        $product->load('providers');
        return view('products.edit', compact('product', 'providers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();
        
        // Calcular el impuesto: precio * (impuesto / 100) / (1 + impuesto / 100)
        // Si el precio ya incluye IVA, extraemos el impuesto
        $precio = $validated['precio'];
        $impuestoPorcentaje = $validated['impuesto'];
        $impuestoCalculado = $precio * ($impuestoPorcentaje / 100) / (1 + ($impuestoPorcentaje / 100));
        
        $validated['impuesto_calculado'] = round($impuestoCalculado, 2);
        
        $product->update($validated);

        // Sincronizar proveedores asociados (filtrar valores vacíos)
        $providers = array_filter($request->input('providers', []), function($value) {
            return !empty($value);
        });
        $product->providers()->sync($providers);

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Producto eliminado correctamente.');
    }
}
