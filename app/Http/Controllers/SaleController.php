<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index()
    {
        $sales = Sale::orderBy('created_at', 'desc')->get();
        
        return view('sales.index', compact('sales'));
    }

    /**
     * Display the POS screen.
     */
    public function pos()
    {
        return view('sales.pos');
    }

    /**
     * Search products by name or code.
     */
    public function searchProducts(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json([]);
        }

        $products = Product::where('nombre', 'like', "%{$query}%")
            ->orWhere('codigo', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'codigo', 'nombre', 'precio', 'existencias']);

        return response()->json($products);
    }

    /**
     * Display the ticket for a sale.
     */
    public function ticket(Sale $sale)
    {
        // Cargar la venta con sus detalles y productos relacionados
        $sale->load('saleDetails.product');
        
        return view('sales.ticket', compact('sale'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(StoreSaleRequest $request)
    {
        $validated = $request->validated();
        $products = $validated['products'];
        $total = $validated['total'];

        try {
            // Usar transacción para asegurar integridad de datos
            DB::beginTransaction();

            // Validar stock suficiente antes de procesar
            foreach ($products as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->existencias < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para el producto {$product->nombre}. Disponible: {$product->existencias}, Solicitado: {$item['quantity']}"
                    ], 422);
                }
            }

            // Crear el registro de venta
            $sale = Sale::create([
                'total' => $total,
            ]);

            // Crear los detalles de venta y descontar stock
            foreach ($products as $item) {
                // Crear detalle de venta
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Descontar existencias del producto
                $product = Product::findOrFail($item['product_id']);
                $product->existencias -= $item['quantity'];
                $product->save();
            }

            // Confirmar transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta registrada correctamente.',
                'sale_id' => $sale->id,
            ], 201);

        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();
            
            Log::error('Error al procesar venta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta. Por favor, intente nuevamente.'
            ], 500);
        }
    }
}
