<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelSaleRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        $query = Sale::with('user', 'cancelledBy')->orderBy('created_at', 'desc');
        
        // Filtro por fecha
        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }
        
        // Filtro por vendedor
        if ($request->filled('vendedor_id')) {
            $query->where('user_id', $request->vendedor_id);
        }
        
        // Si es vendedor, solo mostrar sus ventas
        if (Auth::check() && Auth::user()->role === 'vendedor') {
            $query->where('user_id', Auth::id());
        }
        
        $sales = $query->get();
        $vendedores = User::where('role', 'vendedor')->orderBy('name')->get();
        
        return view('sales.index', compact('sales', 'vendedores'));
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
            ->get(['id', 'codigo', 'nombre', 'precio', 'impuesto', 'impuesto_calculado', 'existencias']);

        return response()->json($products);
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        // Cargar la venta con sus detalles y productos relacionados
        $sale->load('saleDetails.product', 'cancelledBy');
        
        return view('sales.show', compact('sale'));
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
        $totalImpuestos = $validated['total_impuestos'] ?? 0;

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
                'total_impuestos' => $totalImpuestos,
                'user_id' => Auth::id(),
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

    /**
     * Cancel a sale (requires admin credentials).
     */
    public function cancel(CancelSaleRequest $request, Sale $sale)
    {
        // Verificar si la venta ya está cancelada
        if ($sale->cancelada) {
            return back()->with('error', 'Esta venta ya está cancelada.');
        }

        // Validar credenciales de administrador
        $admin = User::where('username', $request->admin_username)
            ->where('role', 'admin')
            ->first();

        if (!$admin || !Hash::check($request->admin_password, $admin->password)) {
            return back()->withErrors([
                'admin_credentials' => 'Las credenciales de administrador son incorrectas.',
            ])->withInput();
        }

        try {
            // Usar transacción para asegurar integridad de datos
            DB::beginTransaction();

            // Cargar los detalles de la venta con productos
            $sale->load('saleDetails.product');

            // Restaurar el inventario de cada producto
            foreach ($sale->saleDetails as $detail) {
                $product = $detail->product;
                $product->existencias += $detail->quantity;
                $product->save();
            }

            // Marcar la venta como cancelada
            $sale->update([
                'cancelada' => true,
                'cancelada_at' => now(),
                'cancelada_por' => $admin->id,
            ]);

            // Confirmar transacción
            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Venta cancelada correctamente. El inventario ha sido restaurado.');
        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();
            
            Log::error('Error al cancelar venta: ' . $e->getMessage());

            return back()->with('error', 'Error al cancelar la venta. Por favor, intente nuevamente.');
        }
    }
}
