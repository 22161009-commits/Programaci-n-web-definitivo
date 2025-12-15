<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
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
        input[type="email"],
        textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus,
        textarea:focus {
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
        textarea {
            resize: vertical;
            min-height: 80px;
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
    </style>
</head>
<body>
    <h1>Editar Proveedor</h1>
    
    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form method="POST" action="{{ route('providers.update', $provider) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">
                Nombre <span class="required">*</span>
            </label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name', $provider->name) }}"
                class="@error('name') error @enderror"
                required
            >
            @error('name')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="contact_name">Nombre de Contacto</label>
            <input 
                type="text" 
                id="contact_name" 
                name="contact_name" 
                value="{{ old('contact_name', $provider->contact_name) }}"
                class="@error('contact_name') error @enderror"
            >
            @error('contact_name')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input 
                type="text" 
                id="phone" 
                name="phone" 
                value="{{ old('phone', $provider->phone) }}"
                class="@error('phone') error @enderror"
            >
            @error('phone')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email', $provider->email) }}"
                class="@error('email') error @enderror"
            >
            @error('email')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="address">Dirección</label>
            <textarea 
                id="address" 
                name="address"
                class="@error('address') error @enderror"
            >{{ old('address', $provider->address) }}</textarea>
            @error('address')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="{{ route('providers.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</body>
</html>

