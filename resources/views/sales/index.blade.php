<!DOCTYPE html>
<html>
<head>
    <title>Historial de Ventas</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .actions {
            text-align: center;
        }
        .view-btn {
            padding: 5px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .view-btn:hover {
            background-color: #0056b3;
        }
        .no-sales {
            text-align: center;
            padding: 20px;
            color: #666;
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
    </style>
</head>
<body>
    <h1>Historial de Ventas</h1>
    
    <div class="links">
        <a href="{{ route('sales.pos') }}">Nueva Venta</a>
        <a href="{{ route('products.index') }}">Productos</a>
        <a href="/">Inicio</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Total</th>
                <th class="actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>#{{ $sale->id }}</td>
                    <td>{{ $sale->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>${{ number_format($sale->total, 2) }}</td>
                    <td class="actions">
                        <a href="{{ route('sales.show', $sale) }}" class="view-btn">Ver Detalle</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="no-sales">No hay ventas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

