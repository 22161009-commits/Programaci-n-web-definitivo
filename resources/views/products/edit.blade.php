<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .error-messages {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .error-messages ul {
            margin: 0;
            padding-left: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .required {
            color: #dc3545;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .price-input-wrapper {
            position: relative;
        }
        .price-input-wrapper::before {
            content: '$';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
        }
        .price-input-wrapper input {
            padding-left: 28px;
        }
        input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .error {
            border-color: #dc3545;
        }
        .error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
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
        select[multiple] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            min-height: 150px;
            background-color: white;
        }
        select[multiple]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        select[multiple] option {
            padding: 8px;
        }
        .select-hint {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Editar Producto</h1>
    
    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="codigo">
                Código <span class="required">*</span>
            </label>
            <input 
                type="text" 
                id="codigo" 
                name="codigo" 
                value="{{ old('codigo', $product->codigo) }}"
                pattern="[0-9]+"
                title="El código solo puede contener números"
                class="@error('codigo') error @enderror"
                required
            >
            @error('codigo')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="nombre">
                Nombre <span class="required">*</span>
            </label>
            <input 
                type="text" 
                id="nombre" 
                name="nombre" 
                value="{{ old('nombre', $product->nombre) }}"
                class="@error('nombre') error @enderror"
                required
            >
            @error('nombre')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="precio">
                Precio <span class="required">*</span>
            </label>
            <div class="price-input-wrapper">
                <input 
                    type="number" 
                    id="precio" 
                    name="precio" 
                    step="0.01"
                    min="0.01"
                    value="{{ old('precio', $product->precio) }}"
                    class="@error('precio') error @enderror"
                    required
                >
            </div>
            @error('precio')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="existencias">
                Existencias <span class="required">*</span>
            </label>
            <input 
                type="number" 
                id="existencias" 
                name="existencias" 
                min="0"
                value="{{ old('existencias', $product->existencias) }}"
                class="@error('existencias') error @enderror"
                required
            >
            @error('existencias')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="providers">
                Proveedores
            </label>
            <select 
                id="providers" 
                name="providers[]" 
                multiple
                class="@error('providers') error @enderror @error('providers.*') error @enderror"
            >
                <option value="" {{ empty(old('providers', $product->providers->pluck('id')->toArray())) ? 'selected' : '' }}>-- No agregar por ahora --</option>
                @if(isset($providers) && $providers->count() > 0)
                    @foreach($providers as $provider)
                        <option 
                            value="{{ $provider->id }}"
                            {{ in_array($provider->id, old('providers', $product->providers->pluck('id')->toArray())) ? 'selected' : '' }}
                        >
                            {{ $provider->name }}@if($provider->contact_name) - {{ $provider->contact_name }}@endif
                        </option>
                    @endforeach
                @else
                    <option disabled>No hay proveedores registrados</option>
                @endif
            </select>
            <div class="select-hint">Mantén presionada la tecla Ctrl (o Cmd en Mac) para seleccionar múltiples proveedores</div>
            @error('providers')
                <div class="error-text">{{ $message }}</div>
            @enderror
            @error('providers.*')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</body>
</html>

