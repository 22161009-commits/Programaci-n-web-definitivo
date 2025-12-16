<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        .card h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .card-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.2s;
        }
        .card-btn:hover {
            transform: scale(1.05);
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Panel de Administración</h1>
            <div class="user-info">
                <span>Bienvenido, {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" style="background: none; border: none; cursor: pointer; color: white; font-size: 14px;">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <div class="dashboard-grid">
            <div class="card">
                <h2>Gestión de Productos</h2>
                <p>Administra el inventario de productos: agregar, editar y eliminar productos del sistema.</p>
                <a href="{{ route('products.index') }}" class="card-btn">Gestionar Productos</a>
            </div>

            <div class="card">
                <h2>Gestión de Proveedores</h2>
                <p>Administra los proveedores y sus relaciones con los productos del inventario.</p>
                <a href="{{ route('providers.index') }}" class="card-btn">Gestionar Proveedores</a>
            </div>

            <div class="card">
                <h2>Reporte de Ventas</h2>
                <p>Visualiza el historial completo de ventas realizadas en el sistema.</p>
                <a href="{{ route('sales.index') }}" class="card-btn">Ver Reporte de Ventas</a>
            </div>
        </div>
    </div>
</body>
</html>

