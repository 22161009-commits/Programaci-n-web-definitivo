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
    <title>Agregar Proveedor</title>
</head>
<body>
    <h1>Agregar Proveedor</h1>
    
    <form method="POST" action="{{ route('providers.store') }}">
        @csrf
        
        <div>
            <label for="name">Nombre: *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        
        <div>
            <label for="contact_name">Nombre de Contacto:</label>
            <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}">
        </div>
        
        <div>
            <label for="phone">Teléfono:</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
        </div>
        
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
        </div>
        
        <div>
            <label for="address">Dirección:</label>
            <textarea id="address" name="address">{{ old('address') }}</textarea>
        </div>
        
        <div>
            <button type="submit">Guardar</button>
        </div>
    </form>
    
    <a href="{{ route('providers.index') }}">Volver a la lista</a>
</body>
</html>

