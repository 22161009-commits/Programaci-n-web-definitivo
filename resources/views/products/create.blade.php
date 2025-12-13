@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<!DOCTYPE html>
<html>
<head>
    <title>Agregar Producto</title>
</head>
<body>
    <h1>Agregar Producto</h1>
    
    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        
        <div>
            <label for="codigo">Código:</label>
            <input type="text" id="codigo" name="codigo" required>
        </div>
        
        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div>
            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" step="0.01" required>
        </div>
        
        <div>
            <label for="existencias">Existencias:</label>
            <input type="number" id="existencias" name="existencias" required>
        </div>
        
        <div>
            <button type="submit">Guardar</button>
        </div>
    </form>
    
    <a href="{{ route('products.index') }}">Volver a la lista</a>
</body>
</html>

