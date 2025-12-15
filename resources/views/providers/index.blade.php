<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
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
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn-edit {
            background-color: #28a745;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <h1>Gestión de Proveedores</h1>
    
    @if (session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif
    
    <a href="{{ route('providers.create') }}" class="btn btn-primary">Agregar Proveedor</a>
    
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Nombre de Contacto</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($providers as $provider)
                <tr>
                    <td>{{ $provider->name }}</td>
                    <td>{{ $provider->contact_name ?? '-' }}</td>
                    <td>{{ $provider->email ?? '-' }}</td>
                    <td>{{ $provider->phone ?? '-' }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('providers.edit', $provider) }}" class="btn-edit">Editar</a>
                            <form method="POST" action="{{ route('providers.destroy', $provider) }}" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">No hay proveedores registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <a href="{{ route('products.index') }}">← Volver a Productos</a>
    </div>
</body>
</html>

