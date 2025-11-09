<x-admin-layout title="Detalhes da Venda">
    <!-- CSS para impressão -->
    <style>
        @media print {
            /* Ocultar elementos não necessários na impressão */
            .no-print, .sidebar, nav, .print-hide, 
            #logo-sidebar, aside, header, 
            .fixed, .sticky, .relative,
            [class*="sidebar"], [id*="sidebar"] {
                display: none !important;
            }
            
            /* Garantir que o conteúdo principal ocupe toda a largura */
            main, .main-content, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: none !important;
            }
            
            /* Ajustar layout para impressão */
            body {
                margin: 0;
                padding: 20px;
                font-size: 12px;
                line-height: 1.4;
            }
            
            .print-container {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
            
            /* Cabeçalho da empresa */
            .company-header {
                text-align: center;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }
            
            /* Dados do cliente */
            .customer-info {
                border: 1px solid #000;
                padding: 10px;
                margin-bottom: 20px;
            }
            
            /* Tabela de produtos */
            .items-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .items-table th, .items-table td {
                border: 1px solid #000;
                padding: 8px;
                text-align: left;
            }
            
            .items-table th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            
            /* Totais */
            .totals-section {
                margin-top: 20px;
                border-top: 2px solid #000;
                padding-top: 10px;
            }
        }
    </style>
    
    <div class="p-4">
        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="p-6 bg-white rounded-lg shadow-md mb-6 print-container">
            <div class="flex justify-between items-center mb-6 no-print">
                <h1 class="text-2xl font-semibold text-gray-900">Detalhes da Venda</h1>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.pos.list') }}" class="px-4 py-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Ver Lista
                    </a>
                    <a href="{{ route('admin.pos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nova Venda
                    </a>
                </div>
            </div>
            
            <!-- Cabeçalho da empresa (visível apenas na impressão) -->
            <div class="company-header" style="display: none;">
                <h1 style="margin: 0; font-size: 24px; font-weight: bold;">{{ config('app.name', 'Loja de Discos') }}</h1>
                <p style="margin: 5px 0;">CNPJ: 00.000.000/0001-00 | Telefone: (00) 0000-0000</p>
                <p style="margin: 5px 0;">Endereço da Loja, Cidade - Estado, CEP 00000-000</p>
            </div>

            <!-- Invoice Header -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                <div class="flex justify-between flex-wrap">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold text-gray-800">Nota de Venda</h2>
                        <p class="text-lg text-gray-800 font-semibold mt-1">{{ $sale->invoice_number }}</p>
                        <p class="text-gray-600 text-sm mt-2">Data: {{ $sale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                
                <!-- Dados completos do cliente -->
                <div class="customer-info bg-white p-4 rounded-lg border mt-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Dados do Cliente</h3>
                    @if($sale->user)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Nome:</p>
                                <p class="text-gray-800">{{ $sale->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Email:</p>
                                <p class="text-gray-800">{{ $sale->user->email }}</p>
                            </div>
                            @if($sale->user->cpf)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">CPF:</p>
                                    <p class="text-gray-800">{{ $sale->user->cpf }}</p>
                                </div>
                            @endif
                            @if($sale->user->phone)
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Telefone:</p>
                                    <p class="text-gray-800">{{ $sale->user->phone }}</p>
                                </div>
                            @endif
                        </div>
                        
                        @if($sale->user->defaultAddress)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-600 mb-2">Endereço:</p>
                                <p class="text-gray-800">
                                    {{ $sale->user->defaultAddress->street }}, {{ $sale->user->defaultAddress->number }}
                                    @if($sale->user->defaultAddress->complement)
                                        , {{ $sale->user->defaultAddress->complement }}
                                    @endif
                                </p>
                                <p class="text-gray-800">
                                    {{ $sale->user->defaultAddress->neighborhood }}, {{ $sale->user->defaultAddress->city }} - {{ $sale->user->defaultAddress->state }}
                                </p>
                                <p class="text-gray-800">CEP: {{ $sale->user->defaultAddress->formatted_zip_code }}</p>
                            </div>
                        @endif
                    @else
                        <div>
                            <p class="text-sm font-medium text-gray-600">Nome:</p>
                            <p class="text-gray-800">{{ $sale->customer_name }}</p>
                            <p class="text-gray-600 text-sm mt-1">Venda direta (sem cadastro)</p>
                        </div>
                    @endif
                </div>
                <div class="flex justify-between flex-wrap mt-4">
                    <div>
                        <h3 class="text-md font-semibold text-gray-800">Método de Pagamento</h3>
                        <p class="text-gray-700">
                            @switch($sale->payment_method)
                                @case('money')
                                    Dinheiro
                                    @break
                                @case('credit')
                                    Cartão de Crédito
                                    @break
                                @case('debit')
                                    Cartão de Débito
                                    @break
                                @case('pix')
                                    PIX
                                    @break
                                @case('transfer')
                                    Transferência Bancária
                                    @break
                                @default
                                    {{ $sale->payment_method }}
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                            Venda Finalizada
                        </span>
                    </div>
                </div>
                @if($sale->notes)
                    <div class="mt-4 p-3 bg-yellow-50 rounded-md">
                        <h3 class="text-md font-semibold text-gray-800">Observações</h3>
                        <p class="text-gray-700">{{ $sale->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Items -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Itens da Venda</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 items-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disco</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço Unitário</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desconto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ asset($item->vinyl->vinylMaster->cover_image ?? 'images/placeholder.jpg') }}" alt="{{ $item->vinyl->vinylMaster->title }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->vinyl->vinylMaster->title }}</div>
                                            <div class="text-sm text-gray-500">
                                                @if($item->vinyl->vinylMaster->artists)
                                                    {{ $item->vinyl->vinylMaster->artists->pluck('name')->implode(', ') }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $item->vinyl->catalog_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    R$ {{ number_format($item->price, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($item->item_discount > 0)
                                        R$ {{ number_format($item->item_discount, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    R$ {{ number_format($item->item_total, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 totals-section">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Subtotal:</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-bold">R$ {{ number_format($sale->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @if($sale->discount > 0)
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Desconto:</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-bold">- R$ {{ number_format($sale->discount, 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($sale->shipping > 0)
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Frete:</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-bold">+ R$ {{ number_format($sale->shipping, 2, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                                <td class="px-6 py-4 text-lg text-green-600 font-bold">R$ {{ number_format($sale->total, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-4 mt-6 no-print">
                <button onclick="printInvoice()" class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimir
                </button>
                <a href="{{ route('admin.pos.list') }}" class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Voltar para Lista
                </a>
            </div>
            
            <script>
                function printInvoice() {
                    // Criar conteúdo HTML limpo para impressão
                    const invoiceContent = `
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Invoice - {{ $sale->invoice_number }}</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    margin: 20px;
                                    padding: 0;
                                    font-size: 12px;
                                    line-height: 1.4;
                                    color: #000;
                                }
                                .company-header {
                                    display: grid;
                                    grid-template-columns: 1fr 2fr 1fr;
                                    gap: 15px;
                                    align-items: center;
                                    border-bottom: 2px solid #000;
                                    padding-bottom: 15px;
                                    margin-bottom: 20px;
                                }
                                .company-header .logo-section {
                                    text-align: left;
                                }
                                .company-header .logo {
                                    max-width: 80px;
                                    max-height: 60px;
                                }
                                .company-header .company-info {
                                    text-align: center;
                                }
                                .company-header .company-info h1 {
                                    margin: 0 0 8px 0;
                                    font-size: 16px;
                                    font-weight: bold;
                                }
                                .company-header .company-info p {
                                    margin: 3px 0;
                                    font-size: 10px;
                                    line-height: 1.3;
                                }
                                .company-header .invoice-info {
                                    text-align: right;
                                }
                                .company-header .invoice-info h2 {
                                    margin: 0 0 5px 0;
                                    font-size: 14px;
                                    font-weight: bold;
                                }
                                .company-header .invoice-info .invoice-number {
                                    font-size: 13px;
                                    font-weight: bold;
                                    margin: 3px 0;
                                }
                                .company-header .invoice-info .invoice-date {
                                    font-size: 10px;
                                    margin: 3px 0;
                                }
                                .invoice-header {
                                    margin-bottom: 20px;
                                }
                                .invoice-number {
                                    font-size: 18px;
                                    font-weight: bold;
                                    margin-bottom: 5px;
                                }
                                .customer-info {
                                    border: 1px solid #000;
                                    padding: 15px;
                                    margin-bottom: 20px;
                                }
                                .customer-info h3 {
                                    margin: 0 0 10px 0;
                                    font-size: 14px;
                                    font-weight: bold;
                                }
                                .customer-grid {
                                    display: grid;
                                    grid-template-columns: 1fr 1fr;
                                    gap: 10px;
                                    margin-bottom: 10px;
                                }
                                .customer-field {
                                    margin-bottom: 8px;
                                }
                                .customer-field .label {
                                    font-weight: bold;
                                    font-size: 11px;
                                    color: #666;
                                }
                                .customer-field .value {
                                    font-size: 12px;
                                    color: #000;
                                }
                                .address-section {
                                    border-top: 1px solid #ccc;
                                    padding-top: 10px;
                                    margin-top: 10px;
                                }
                                .items-table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-bottom: 20px;
                                }
                                .items-table th, .items-table td {
                                    border: 1px solid #000;
                                    padding: 8px;
                                    text-align: left;
                                }
                                .items-table th {
                                    background-color: #f0f0f0;
                                    font-weight: bold;
                                    font-size: 11px;
                                }
                                .items-table td {
                                    font-size: 11px;
                                }
                                .totals-section {
                                    border-top: 2px solid #000;
                                    padding-top: 10px;
                                }
                                .total-row {
                                    font-weight: bold;
                                    font-size: 14px;
                                }
                                .payment-method {
                                    margin-top: 15px;
                                    padding: 10px;
                                    border: 1px solid #ccc;
                                }
                                .notes-section {
                                    margin-top: 15px;
                                    padding: 10px;
                                    border: 1px solid #ccc;
                                    background-color: #f9f9f9;
                                }
                            </style>
                        </head>
                        <body>
                            <!-- Cabeçalho da empresa em 3 colunas -->
                            <div class="company-header">
                                <!-- Coluna 1: Logo (Esquerda) -->
                                <div class="logo-section">
                                    <img src="{{ asset('images/logo.png') }}" alt="Logo RDV Discos" class="logo">
                                </div>
                                
                                <!-- Coluna 2: Dados da Empresa (Centro) -->
                                <div class="company-info">
                                    <h1>RDV DISCOS DE VINIL</h1>
                                    <p><strong>CNPJ:</strong> 61.850.546/0001-26</p>
                                    <p><strong>Telefone:</strong> (11) 94715-9293</p>
                                    <p>Rua Montevidéu, 174 - Santo André - SP</p>
                                    <p>CEP: 09220-360</p>
                                </div>
                                
                                <!-- Coluna 3: Informações do Invoice (Direita) -->
                                <div class="invoice-info">
                                    <h2>NOTA DE VENDA</h2>
                                    <div class="invoice-number">#{{ $sale->invoice_number }}</div>
                                    <div class="invoice-date">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>

                            <!-- Dados do Cliente -->
                            <div class="customer-info">
                                <h3>Dados do Cliente</h3>
                                @if($sale->user)
                                    <div class="customer-grid">
                                        <div class="customer-field">
                                            <div class="label">Nome:</div>
                                            <div class="value">{{ $sale->user->name }}</div>
                                        </div>
                                        <div class="customer-field">
                                            <div class="label">Email:</div>
                                            <div class="value">{{ $sale->user->email }}</div>
                                        </div>
                                        @if($sale->user->cpf)
                                            <div class="customer-field">
                                                <div class="label">CPF:</div>
                                                <div class="value">{{ $sale->user->cpf }}</div>
                                            </div>
                                        @endif
                                        @if($sale->user->phone)
                                            <div class="customer-field">
                                                <div class="label">Telefone:</div>
                                                <div class="value">{{ $sale->user->phone }}</div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($sale->user->defaultAddress)
                                        <div class="address-section">
                                            <div class="label">Endereço:</div>
                                            <div class="value">
                                                {{ $sale->user->defaultAddress->street }}, {{ $sale->user->defaultAddress->number }}
                                                @if($sale->user->defaultAddress->complement), {{ $sale->user->defaultAddress->complement }}@endif<br>
                                                {{ $sale->user->defaultAddress->neighborhood }}, {{ $sale->user->defaultAddress->city }} - {{ $sale->user->defaultAddress->state }}<br>
                                                CEP: {{ $sale->user->defaultAddress->formatted_zip_code }}
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="customer-field">
                                        <div class="label">Nome:</div>
                                        <div class="value">{{ $sale->customer_name }}</div>
                                        <div style="font-style: italic; color: #666; font-size: 11px;">Venda direta (sem cadastro)</div>
                                    </div>
                                @endif
                            </div>

                            <!-- Método de Pagamento -->
                            <div class="payment-method">
                                <strong>Método de Pagamento:</strong>
                                @switch($sale->payment_method)
                                    @case('money') Dinheiro @break
                                    @case('credit') Cartão de Crédito @break
                                    @case('debit') Cartão de Débito @break
                                    @case('pix') PIX @break
                                    @case('transfer') Transferência Bancária @break
                                    @default {{ $sale->payment_method }}
                                @endswitch
                            </div>

                            <!-- Itens da Venda -->
                            <h3 style="margin: 20px 0 10px 0;">Itens da Venda</h3>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Disco</th>
                                        <th>Preço Unitário</th>
                                        <th>Desconto</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->vinyl->vinylMaster->title }}</strong><br>
                                            @if($item->vinyl->vinylMaster->artists)
                                                <small>{{ $item->vinyl->vinylMaster->artists->pluck('name')->implode(', ') }}</small><br>
                                            @endif
                                            <small style="color: #666;">{{ $item->vinyl->catalog_number }}</small>
                                        </td>
                                        <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td>
                                            @if($item->item_discount > 0)
                                                R$ {{ number_format($item->item_discount, 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><strong>R$ {{ number_format($item->item_total, 2, ',', '.') }}</strong></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="totals-section">
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                                        <td><strong>R$ {{ number_format($sale->subtotal, 2, ',', '.') }}</strong></td>
                                    </tr>
                                    @if($sale->discount > 0)
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Desconto:</strong></td>
                                        <td style="color: red;"><strong>- R$ {{ number_format($sale->discount, 2, ',', '.') }}</strong></td>
                                    </tr>
                                    @endif
                                    @if($sale->shipping > 0)
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><strong>Frete:</strong></td>
                                        <td><strong>+ R$ {{ number_format($sale->shipping, 2, ',', '.') }}</strong></td>
                                    </tr>
                                    @endif
                                    <tr class="total-row">
                                        <td colspan="3" style="text-align: right; font-size: 16px;"><strong>TOTAL:</strong></td>
                                        <td style="font-size: 16px; color: green;"><strong>R$ {{ number_format($sale->total, 2, ',', '.') }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>

                            @if($sale->notes)
                            <div class="notes-section">
                                <strong>Observações:</strong><br>
                                {{ $sale->notes }}
                            </div>
                            @endif
                        </body>
                        </html>
                    `;

                    // Abrir nova janela com o conteúdo do invoice
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(invoiceContent);
                    printWindow.document.close();
                    
                    // Aguardar o carregamento e imprimir automaticamente
                    printWindow.onload = function() {
                        setTimeout(() => {
                            printWindow.print();
                        }, 500);
                    };
                }
            </script>
        </div>
    </div>
</x-admin-layout>
