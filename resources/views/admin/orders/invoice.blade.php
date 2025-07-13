<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura #{{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin: 20px auto;
            padding: 30px;
            max-width: 1000px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .invoice-number {
            font-size: 18px;
            color: #666;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-section h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 16px;
            color: #2c3e50;
        }
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        .info-col {
            flex: 1;
            padding: 0 15px;
            min-width: 250px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            margin-right: 5px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #2c3e50;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .total-label {
            font-weight: bold;
            color: #555;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            border-top: 2px solid #333;
            padding-top: 15px;
            color: #2c3e50;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .actions {
            margin: 30px 0;
            text-align: center;
        }
        .btn-print {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-print:hover {
            background-color: #2980b9;
        }
        
        /* Estilos específicos para impressão */
        @media print {
            body {
                background-color: #fff;
                font-size: 12pt;
            }
            .container {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .actions {
                display: none;
            }
            .no-print {
                display: none;
            }
            a {
                text-decoration: none;
                color: #333;
            }
            .page-break {
                page-break-after: always;
            }
            @page {
                margin: 1.5cm;
                size: A4;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="actions no-print flex space-x-4 mb-6">
            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Fatura
            </button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Voltar para Detalhes do Pedido
            </a>
        </div>
        <div class="header">
            <h2 class="mb-3">Multilojas Ale</h2>
            <img src="{{ asset('images/logo.png') }}" alt="Logo da Loja" class="logo">
            
            <div class="invoice-title">FATURA</div>
            <div class="invoice-number">Pedido #{{ $order->order_number }}</div>
            <div>Data da Fatura: {{ now()->format('d/m/Y') }}</div>
            <div>Data do Pedido: {{ $order->created_at->format('d/m/Y H:i') }}</div>
        </div>

        <div class="info-grid">
            <div class="info-col">
                <div class="info-section">
                    <h3>DADOS DO CLIENTE</h3>
                    <div class="info-item">
                        <span class="info-label">Nome:</span> {{ $order->user->name }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span> {{ $order->user->email }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Telefone:</span> {{ $order->user->phone ?? 'Não informado' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">CPF/CNPJ:</span> {{ $order->user->document ?? 'Não informado' }}
                    </div>
                </div>
            </div>

            <div class="info-col">
                <div class="info-section">
                    <h3>ENDEREÇO DE ENTREGA</h3>
                    @if($order->address)
                        <div class="info-item">
                            <span class="info-label">Destinatário:</span> {{ $order->address->recipient_name ?? $order->user->name }}
                        </div>
                        <div class="info-item">
                            <span class="info-label">CEP:</span> {{ $order->address->zipcode }}
                        </div>
                        <div class="info-item">
                            <span class="info-label">Endereço:</span> {{ $order->address->street }}, {{ $order->address->number }}
                            @if($order->address->complement)
                                - {{ $order->address->complement }}
                            @endif
                        </div>
                        <div class="info-item">
                            <span class="info-label">Bairro:</span> {{ $order->address->neighborhood }}
                        </div>
                        <div class="info-item">
                            <span class="info-label">Cidade/UF:</span> {{ $order->address->city }}/{{ $order->address->state }}
                        </div>
                    @else
                        <div class="info-item">Nenhum endereço de entrega informado.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-col">
                <div class="info-section">
                    <h3>INFORMAÇÕES DE PAGAMENTO</h3>
                    <div class="info-item">
                        <span class="info-label">Método:</span> 
                        @if($order->payment)
                            @switch($order->payment->payment_method)
                                @case('credit_card')
                                    Cartão de Crédito
                                    @break
                                @case('pix')
                                    PIX
                                    @break
                                @case('boleto')
                                    Boleto Bancário
                                    @break
                                @default
                                    {{ ucfirst($order->payment->payment_method) }}
                            @endswitch
                        @else
                            Não informado
                        @endif
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span> 
                        {{ $order->payment_status == 'approved' ? 'Aprovado' : ucfirst($order->payment_status ?? 'Pendente') }}
                    </div>
                    @if($order->payment && $order->payment->transaction_id)
                        <div class="info-item">
                            <span class="info-label">ID da Transação:</span> {{ $order->payment->transaction_id }}
                        </div>
                    @endif
                    @if($order->payment && $order->payment->paid_at)
                        <div class="info-item">
                            <span class="info-label">Data do Pagamento:</span> {{ $order->payment->paid_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="info-col">
                <div class="info-section">
                    <h3>INFORMAÇÕES DE ENVIO</h3>
                    <div class="info-item">
                        <span class="info-label">Método:</span> {{ $order->shipping_method ?? 'Padrão' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        @switch($order->shipping_status)
                            @case('pending')
                                Pendente
                                @break
                            @case('label_generated')
                                Etiqueta Gerada
                                @break
                            @case('shipped')
                                Enviado
                                @break
                            @case('delivered')
                                Entregue
                                @break
                            @case('cancelled')
                                Cancelado
                                @break
                            @default
                                {{ ucfirst($order->shipping_status ?? 'Pendente') }}
                        @endswitch
                    </div>
                    @if($order->shipping_tracking)
                        <div class="info-item">
                            <span class="info-label">Código de Rastreamento:</span> {{ $order->shipping_tracking }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>ITENS DO PEDIDO</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Produto</th>
                        <th>Preço Unit.</th>
                        <th>Qtd</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->vinylMaster)
                                        <div>{{ $item->vinylMaster->title }}</div>
                                        @if($item->vinylMaster->artists)
                                            <div>
                                                @foreach($item->vinylMaster->artists as $artist)
                                                    {{ $artist->name }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="text-right">R$ {{ number_format($item->unit_price ?? 0, 2, ',', '.') }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">R$ {{ number_format(($item->unit_price ?? 0) * $item->quantity, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span>R$ {{ number_format($order->subtotal ?? 0, 2, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Frete:</span>
                <span>R$ {{ number_format($order->shipping ?? 0, 2, ',', '.') }}</span>
            </div>
            @if($order->discount > 0)
                <div class="total-row">
                    <span class="total-label">Desconto:</span>
                    <span>-R$ {{ number_format($order->discount ?? 0, 2, ',', '.') }}</span>
                </div>
            @endif
            <div class="grand-total">
                <span class="total-label">TOTAL:</span>
                <span>R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>
                Multilojas Ale - 
                CNPJ: 12.345.678/0001-90
            </p>
            <p>
                Av. Paulista, 1000 - Bela Vista, São Paulo/SP - 
                Tel: (11) 3456-7890
            </p>
            <p>
                contato@multilojasale.com.br - 
                www.multilojasale.com.br
            </p>
            <p>Este documento não possui valor fiscal - Emitido em {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
