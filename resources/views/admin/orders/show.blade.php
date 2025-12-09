<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalhes do Pedido #{{ $order->order_number }}
        </h2>
    </x-slot>
    
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-gray-900">Detalhes do Pedido #{{ $order->order_number }}</h1>
    
    <nav class="flex mt-2" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <a href="{{ route('admin.orders.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Pedidos</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Detalhes do Pedido #{{ $order->order_number }}</span>
                </div>
            </li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <span class="block sm:inline session-success-message">{{ session('success') }}</span>
            @if(session('label_url'))
                <a href="{{ session('label_url') }}" target="_blank" class="ml-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Ver Etiqueta
                </a>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <span class="block sm:inline session-error-message">{{ session('error') }}</span>
        </div>
    @endif
    
    <x-toast-alert />

    <div class="mt-6">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Status do Pedido</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.orders.invoice', $order->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Gerar Fatura
                    </a>
                    <form action="{{ route('admin.orders.generate-shipping-label', $order->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                        Gerar Etiqueta
                    </button>
                    </form>
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="flex">
                                <select name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Aguardando Pagamento</option>
                                        <option value="payment_approved" {{ $order->status == 'payment_approved' ? 'selected' : '' }}>Pagamento Aprovado</option>
                                        <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Em Preparação</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregue</option>
                                        <option value="canceled" {{ $order->status == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Atualizar Status
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div>
                            <div class="flex justify-end">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $order->status == 'canceled' ? 'bg-red-100 text-red-800' : 
                                       ($order->status == 'delivered' ? 'bg-green-100 text-green-800' : 
                                       'bg-blue-100 text-blue-800') }}">
                                    Status Atual: {{ $order->status ? App\Http\Controllers\Admin\OrdersController::getStatusLabel($order->status) : 'Desconhecido' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informações do Cliente -->
        <div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg h-full">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informações do Cliente</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->user->phone ?? 'Não informado' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Data do Pedido</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Informações de Pagamento -->
        <div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg h-full">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informações de Pagamento</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <div class="mb-4">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Método de Pagamento</dt>
                        <dd class="mt-1">
                            @if($order->payment)
                                <form action="{{ route('admin.orders.update-payment-method', $order->id) }}" method="POST" class="flex">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex">
                                        <select name="payment_method" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                            <option value="credit_card" {{ $order->payment->payment_method == 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                                            <option value="pix" {{ $order->payment->payment_method == 'pix' ? 'selected' : '' }}>PIX</option>
                                            <option value="boleto" {{ $order->payment->payment_method == 'boleto' ? 'selected' : '' }}>Boleto</option>
                                        </select>
                                        <button type="submit" class="ml-3 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Atualizar
                                        </button>
                                    </div>
                                </form>
                            @else
                                <span class="text-sm text-gray-900">Não informado</span>
                            @endif
                        </dd>
                    </div>
                    <div class="mb-4">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status do Pagamento</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->payment_status == 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $order->payment_status == 'approved' ? 'Aprovado' : ucfirst($order->payment_status ?? 'Pendente') }}
                            </span>
                        </dd>
                    </div>
                    
                    @if($order->payment)
                    <div class="mb-4">
                        <dt class="text-sm font-medium text-gray-500 mb-1">ID da Transação</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->transaction_id ?? 'Não informado' }}</dd>
                    </div>
                    
                    <div class="mb-4">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Data do Pagamento</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->payment->paid_at ? $order->payment->paid_at->format('d/m/Y H:i') : 'Não informado' }}</dd>
                    </div>
                    @endif
                    
                    <div class="mb-4">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Valor Total</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-bold">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Endereço de Entrega -->
        <div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg h-full">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Endereço de Entrega</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    @if($order->address)
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Destinatário</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->address->recipient_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">CEP</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->address->zipcode }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Endereço</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->address->street }}, {{ $order->address->number }}</dd>
                            </div>
                            @if($order->address->complement)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Complemento</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->address->complement }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Bairro</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->address->neighborhood }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Cidade/UF</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->address->city }}/{{ $order->address->state }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-500">Nenhum endereço de entrega informado.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informações de Envio -->
        <div>
            <div class="bg-white shadow overflow-hidden sm:rounded-lg h-full">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Informações de Envio</h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status de Envio</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $order->shipping_status == 'delivered' ? 'bg-green-100 text-green-800' : 
                                      ($order->shipping_status == 'shipped' ? 'bg-blue-100 text-blue-800' : 
                                      'bg-yellow-100 text-yellow-800') }}">
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
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Método de Envio</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->shipping_method ?? 'Padrão' }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Valor do Frete</dt>
                            <dd class="mt-1 text-sm text-gray-900">R$ {{ number_format($order->shipping ?? 0, 2, ',', '.') }}</dd>
                        </div>
                        
                        @if($order->shipping_tracking)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Código de Rastreamento</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $order->shipping_tracking }}</dd>
                        </div>
                        @endif
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Etiqueta de Envio</dt>
                            <dd class="mt-1 flex space-x-2">
                                @if($order->shipping_label_url)
                                <a href="{{ $order->shipping_label_url }}" target="_blank" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Ver Etiqueta
                                </a>
                                @endif
                                
                                <form action="{{ route('admin.orders.generate-shipping-label', $order->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        {{ $order->shipping_label_url ? 'Gerar Nova Etiqueta' : 'Gerar Etiqueta' }}
                                    </button>
                                </form>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Itens do Pedido -->
    <div class="mt-6">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Itens do Pedido</h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artista(s)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço Unitário</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 bg-gray-300 rounded"></div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product_name ?? $item->album_title ?? 'Produto' }}
                                            </div>
                                            @if($item->artist_name || $item->album_title)
                                                <div class="text-sm text-gray-500">
                                                    @if($item->artist_name)
                                                        {{ $item->artist_name }}
                                                        @if($item->album_title)
                                                            -
                                                        @endif
                                                    @endif
                                                    {{ $item->album_title }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($item->artist_name)
                                        {{ $item->artist_name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($item->unit_price ?? 0, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($item->total_price ?? (($item->unit_price ?? 0) * $item->quantity), 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal:</td>
                            <td class="px-6 py-3 text-sm text-gray-500">R$ {{ number_format($order->subtotal ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Frete:</td>
                            <td class="px-6 py-3 text-sm text-gray-500">R$ {{ number_format($order->shipping ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        @if($order->discount > 0)
                            <tr>
                                <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Desconto:</td>
                                <td class="px-6 py-3 text-sm text-gray-500">-R$ {{ number_format($order->discount ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total:</td>
                            <td class="px-6 py-3 text-sm font-bold text-gray-900">R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Histórico do Pedido -->
    <div class="mt-6">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Histórico do Pedido</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Pedido criado</p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $order->created_at->format('Y-m-d H:i') }}">{{ $order->created_at->format('d/m/Y H:i') }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <!-- Adicione mais itens de histórico conforme necessário -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</x-admin-layout>