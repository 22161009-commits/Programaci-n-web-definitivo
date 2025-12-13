<!DOCTYPE html>
<html>
<head>
    <title>Productos</title>
</head>
<body>
    <h1>Productos</h1>
    
    <a href="{{ route('products.create') }}">Agregar producto</a>
    
    <table border="1">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Existencias</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->codigo }}</td>
                    <td>{{ $product->nombre }}</td>
                    <td>{{ $product->precio }}</td>
                    <td>{{ $product->existencias }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay productos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

