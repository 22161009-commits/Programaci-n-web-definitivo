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
    <title>Editar Producto</title>
</head>
<body>
    <h1>Editar Producto</h1>
    
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')
        
        <div>
            <label for="codigo">Código:</label>
            <input type="text" id="codigo" name="codigo" value="{{ old('codigo', $product->codigo) }}" required>
        </div>
        
        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $product->nombre) }}" required>
        </div>
        
        <div>
            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" step="0.01" value="{{ old('precio', $product->precio) }}" required>
        </div>
        
        <div>
            <label for="existencias">Existencias:</label>
            <input type="number" id="existencias" name="existencias" value="{{ old('existencias', $product->existencias) }}" required>
        </div>
        
        <div>
            <button type="submit">Actualizar</button>
        </div>
    </form>
    
    <a href="{{ route('products.index') }}">Volver a la lista</a>
</body>
</html>

