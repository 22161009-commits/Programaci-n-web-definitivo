<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta #{{ $sale->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 300px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
        }

        .ticket {
            border: 1px solid #000;
            padding: 15px;
            background-color: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
        }

        .sale-info {
            margin-bottom: 10px;
            font-size: 10px;
        }

        .sale-info p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }

        table thead {
            border-bottom: 1px solid #000;
        }

        table th {
            text-align: left;
            padding: 5px 0;
            font-weight: bold;
        }

        table td {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        table td:last-child {
            text-align: right;
        }

        .total-section {
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 10px;
            text-align: right;
        }

        .total-section .total-label {
            font-size: 14px;
            font-weight: bold;
        }

        .total-section .total-amount {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }

        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color: #0056b3;
        }

        /* Estilos para impresión */
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }

            .print-button {
                display: none;
            }

            .ticket {
                border: none;
                padding: 0;
            }

            @page {
                size: 80mm auto;
                margin: 5mm;
            }
        }

        /* Ocultar elementos no necesarios en impresión */
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Encabezado -->
        <div class="header">
            <h1>TIENDA POS</h1>
            <p>Punto de Venta</p>
            <p>Tel: (123) 456-7890</p>
        </div>

        <!-- Información de la venta -->
        <div class="sale-info">
            <p><strong>Ticket #:</strong> {{ $sale->id }}</p>
            <p><strong>Fecha:</strong> {{ $sale->created_at->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</p>
        </div>

        <!-- Tabla de productos -->
        <table>
            <thead>
                <tr>
                    <th>Cant.</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleDetails as $detail)
                    <tr>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ $detail->product->nombre }}</td>
                        <td>${{ number_format($detail->price, 2) }}</td>
                        <td>${{ number_format($detail->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="total-section">
            <div style="margin-bottom: 5px;">
                <span style="font-size: 11px;">Subtotal: ${{ number_format($sale->total - ($sale->total_impuestos ?? 0), 2) }}</span>
            </div>
            <div style="margin-bottom: 5px;">
                <span style="font-size: 11px;">Impuestos: ${{ number_format($sale->total_impuestos ?? 0, 2) }}</span>
            </div>
            <div class="total-label" style="margin-top: 10px; border-top: 1px solid #000; padding-top: 5px;">TOTAL:</div>
            <div class="total-amount">${{ number_format($sale->total, 2) }}</div>
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p>¡Gracias por su compra!</p>
            <p>Vuelva pronto</p>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="print-button no-print" style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
        <button onclick="window.print()">Imprimir Ticket</button>
        <button onclick="window.location.href='{{ route('sales.pos') }}'" style="background-color: #28a745;">Nueva Venta</button>
    </div>
</body>
</html>

