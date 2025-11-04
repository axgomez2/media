@component('mail::message')
# ✅ Pagamento Aprovado!

Olá **{{ $order->user->name ?? 'Cliente' }}**,

Temos uma ótima notícia! Seu pagamento foi **aprovado com sucesso**! 🎉

## Detalhes do Pedido

**Número do Pedido:** #{{ $order->order_number }}  
**Data:** {{ $order->created_at->format('d/m/Y H:i') }}  
**Valor Total:** R$ {{ number_format($order->total, 2, ',', '.') }}

### Produtos
@foreach($order->items as $item)
- **{{ $item->product_name }}** ({{ $item->artist_name ?? '' }})
  - Quantidade: {{ $item->quantity }}
  - Preço unitário: R$ {{ number_format($item->unit_price, 2, ',', '.') }}
@endforeach

---

**Subtotal:** R$ {{ number_format($order->subtotal, 2, ',', '.') }}  
**Frete:** R$ {{ number_format($order->shipping_cost, 2, ',', '.') }}  
@if($order->discount > 0)
**Desconto:** -R$ {{ number_format($order->discount, 2, ',', '.') }}
@endif
**Total:** R$ {{ number_format($order->total, 2, ',', '.') }}

## Próximos Passos

Seu pedido está sendo preparado para envio. Você receberá um novo e-mail assim que seu pedido for enviado com o código de rastreamento.

@component('mail::button', ['url' => config('app.frontend_url') . '/minha-conta/pedidos'])
Ver Meus Pedidos
@endcomponent

---

Obrigado por comprar conosco!

**{{ config('app.name') }}**
@endcomponent
