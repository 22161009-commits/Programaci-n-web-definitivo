<!DOCTYPE html>
<html>
<head>
    <title>Proveedores</title>
</head>
<body>
    <h1>Proveedores</h1>
    
    @if (session('success'))
        <div style="color: green;">
            {{ session('success') }}
        </div>
    @endif
    
    <a href="{{ route('providers.create') }}">Agregar proveedor</a>
    
    <table border="1">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($providers as $provider)
                <tr>
                    <td>{{ $provider->name }}</td>
                    <td>{{ $provider->email ?? '-' }}</td>
                    <td>{{ $provider->phone ?? '-' }}</td>
                    <td>
                        <a href="{{ route('providers.edit', $provider) }}">Editar</a>
                        <form method="POST" action="{{ route('providers.destroy', $provider) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No hay proveedores registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <a href="{{ route('products.index') }}">Volver a Productos</a>
</body>
</html>

