<!DOCTYPE html>
<html>
<head>
    <title>Detalle de Venta #{{ $sale->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            margin-bottom: 20px;
        }
        .sale-header {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .sale-header h2 {
            margin-top: 0;
            color: #333;
        }
        .sale-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }
        .links {
            margin-bottom: 20px;
        }
        .links a {
            margin-right: 15px;
            color: #007bff;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .btn {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>Detalle de Venta</h1>
    
    <div class="links">
        <a href="{{ route('sales.index') }}">← Volver al Historial</a>
        <a href="{{ route('sales.pos') }}">Nueva Venta</a>
        <a href="{{ route('products.index') }}">Productos</a>
    </div>
    
    <!-- Encabezado con datos de la venta -->
    <div class="sale-header">
        <h2>Venta #{{ $sale->id }}</h2>
        <div class="sale-info">
            <div class="info-item">
                <span class="info-label">Fecha</span>
                <span class="info-value">{{ $sale->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ID de Venta</span>
                <span class="info-value">#{{ $sale->id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Total</span>
                <span class="info-value">${{ number_format($sale->total, 2) }}</span>
            </div>
        </div>
    </div>
    
    <!-- Tabla de productos -->
    <h2>Productos Vendidos</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre del Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->saleDetails as $detail)
                <tr>
                    <td>{{ $detail->product->nombre }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>${{ number_format($detail->price, 2) }}</td>
                    <td>${{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">
                        No hay productos en esta venta.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Total -->
    <div class="total-section">
        <span>Total de la Venta: ${{ number_format($sale->total, 2) }}</span>
    </div>
    
    <!-- Botones de acción -->
    <div style="margin-top: 20px;">
        <a href="{{ route('sales.ticket', $sale) }}" class="btn btn-primary">Ver Ticket</a>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Volver al Historial</a>
    </div>
</body>
</html>

