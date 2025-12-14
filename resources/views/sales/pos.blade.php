<!DOCTYPE html>
<html>
<head>
    <title>Punto de Venta (POS)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .search-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .search-section input {
            padding: 8px;
            width: 300px;
            margin-right: 10px;
        }
        .search-section button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .search-section button:hover {
            background-color: #0056b3;
        }
        .product-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .add-product-section {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #fff;
        }
        .add-product-section input {
            padding: 8px;
            margin-right: 10px;
            width: 100px;
        }
        .add-product-section button {
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        .add-product-section button:hover {
            background-color: #218838;
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
        }
        .quantity-input {
            width: 60px;
            padding: 5px;
        }
        .remove-btn {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
        .total-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 2px solid #007bff;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }
        .error {
            color: red;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Punto de Venta (POS)</h1>
    
    <a href="/">Volver al inicio</a>
    
    <!-- Sección de búsqueda de productos -->
    <div class="search-section">
        <h2>Buscar Producto</h2>
        <input type="text" id="searchInput" placeholder="Buscar por nombre o código..." />
        <button onclick="searchProduct()">Buscar</button>
        <div id="productInfo" class="product-info" style="display: none;"></div>
        <div id="searchError" class="error"></div>
    </div>
    
    <!-- Sección para agregar producto a la venta -->
    <div class="add-product-section" id="addProductSection" style="display: none;">
        <h3>Agregar a la Venta</h3>
        <label>Cantidad: </label>
        <input type="number" id="quantityInput" min="1" value="1" />
        <button onclick="addToSale()">Agregar Producto</button>
        <div id="addError" class="error"></div>
    </div>
    
    <!-- Tabla de productos en la venta -->
    <h2>Productos en la Venta</h2>
    <table id="saleTable">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio Unitario</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="saleTableBody">
            <tr>
                <td colspan="6" style="text-align: center;">No hay productos en la venta</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Total general -->
    <div class="total-section">
        <span>Total: $<span id="totalAmount">0.00</span></span>
    </div>
    
    <script>
        // Variable para almacenar el producto seleccionado
        let selectedProduct = null;
        
        // Array para almacenar los productos en la venta
        let saleItems = [];
        
        /**
         * Buscar producto por nombre o código
         */
        function searchProduct() {
            const query = document.getElementById('searchInput').value.trim();
            const errorDiv = document.getElementById('searchError');
            const productInfoDiv = document.getElementById('productInfo');
            const addProductSection = document.getElementById('addProductSection');
            
            // Limpiar mensajes anteriores
            errorDiv.textContent = '';
            productInfoDiv.style.display = 'none';
            addProductSection.style.display = 'none';
            selectedProduct = null;
            
            if (!query) {
                errorDiv.textContent = 'Por favor ingrese un nombre o código para buscar.';
                return;
            }
            
            // Realizar búsqueda AJAX
            fetch(`{{ route('sales.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(products => {
                    if (products.length === 0) {
                        errorDiv.textContent = 'No se encontraron productos.';
                        return;
                    }
                    
                    // Tomar el primer resultado (en producción podrías mostrar una lista)
                    const product = products[0];
                    selectedProduct = product;
                    
                    // Mostrar información del producto
                    productInfoDiv.innerHTML = `
                        <strong>Producto encontrado:</strong><br>
                        Código: ${product.codigo}<br>
                        Nombre: ${product.nombre}<br>
                        Precio: $${parseFloat(product.precio).toFixed(2)}<br>
                        Existencias: ${product.existencias}
                    `;
                    productInfoDiv.style.display = 'block';
                    
                    // Mostrar sección para agregar
                    addProductSection.style.display = 'block';
                    document.getElementById('quantityInput').value = 1;
                    document.getElementById('addError').textContent = '';
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorDiv.textContent = 'Error al buscar producto.';
                });
        }
        
        /**
         * Permitir búsqueda con Enter
         */
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProduct();
            }
        });
        
        /**
         * Agregar producto a la venta
         */
        function addToSale() {
            const quantity = parseInt(document.getElementById('quantityInput').value);
            const errorDiv = document.getElementById('addError');
            
            if (!selectedProduct) {
                errorDiv.textContent = 'Por favor busque un producto primero.';
                return;
            }
            
            if (!quantity || quantity <= 0) {
                errorDiv.textContent = 'La cantidad debe ser mayor a 0.';
                return;
            }
            
            if (quantity > selectedProduct.existencias) {
                errorDiv.textContent = `Solo hay ${selectedProduct.existencias} unidades disponibles.`;
                return;
            }
            
            // Verificar si el producto ya está en la venta
            const existingIndex = saleItems.findIndex(item => item.product_id === selectedProduct.id);
            
            if (existingIndex !== -1) {
                // Si ya existe, sumar la cantidad
                saleItems[existingIndex].quantity += quantity;
                
                // Validar que no exceda existencias
                if (saleItems[existingIndex].quantity > selectedProduct.existencias) {
                    errorDiv.textContent = `No hay suficientes existencias. Máximo: ${selectedProduct.existencias}`;
                    saleItems[existingIndex].quantity -= quantity; // Revertir
                    return;
                }
            } else {
                // Agregar nuevo producto a la venta
                saleItems.push({
                    product_id: selectedProduct.id,
                    codigo: selectedProduct.codigo,
                    nombre: selectedProduct.nombre,
                    price: parseFloat(selectedProduct.precio),
                    quantity: quantity,
                    existencias: selectedProduct.existencias
                });
            }
            
            // Limpiar errores y actualizar tabla
            errorDiv.textContent = '';
            updateSaleTable();
            calculateTotal();
            
            // Limpiar búsqueda
            document.getElementById('searchInput').value = '';
            document.getElementById('productInfo').style.display = 'none';
            document.getElementById('addProductSection').style.display = 'none';
            selectedProduct = null;
        }
        
        /**
         * Actualizar tabla de productos en la venta
         */
        function updateSaleTable() {
            const tbody = document.getElementById('saleTableBody');
            
            if (saleItems.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay productos en la venta</td></tr>';
                return;
            }
            
            tbody.innerHTML = saleItems.map((item, index) => {
                const subtotal = item.price * item.quantity;
                return `
                    <tr>
                        <td>${item.codigo}</td>
                        <td>${item.nombre}</td>
                        <td>$${item.price.toFixed(2)}</td>
                        <td>
                            <input type="number" 
                                   class="quantity-input" 
                                   value="${item.quantity}" 
                                   min="1" 
                                   max="${item.existencias}"
                                   onchange="updateQuantity(${index}, this.value)" />
                        </td>
                        <td>$${subtotal.toFixed(2)}</td>
                        <td>
                            <button class="remove-btn" onclick="removeItem(${index})">Eliminar</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        /**
         * Actualizar cantidad de un producto
         */
        function updateQuantity(index, newQuantity) {
            const quantity = parseInt(newQuantity);
            const item = saleItems[index];
            
            if (!quantity || quantity <= 0) {
                alert('La cantidad debe ser mayor a 0.');
                updateSaleTable();
                return;
            }
            
            if (quantity > item.existencias) {
                alert(`Solo hay ${item.existencias} unidades disponibles.`);
                updateSaleTable();
                return;
            }
            
            item.quantity = quantity;
            updateSaleTable();
            calculateTotal();
        }
        
        /**
         * Eliminar producto de la venta
         */
        function removeItem(index) {
            if (confirm('¿Está seguro de eliminar este producto de la venta?')) {
                saleItems.splice(index, 1);
                updateSaleTable();
                calculateTotal();
            }
        }
        
        /**
         * Calcular total general
         */
        function calculateTotal() {
            const total = saleItems.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
            
            document.getElementById('totalAmount').textContent = total.toFixed(2);
        }
    </script>
</body>
</html>

