<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho Abandonado - RDV Discos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .cart-item {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .item-info {
            flex: 1;
            margin-left: 15px;
        }
        .item-title {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .item-artist {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .item-price {
            color: #059669;
            font-weight: bold;
            font-size: 16px;
        }
        .total {
            background-color: #4f46e5;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
        }
        .cta-button {
            display: inline-block;
            background-color: #059669;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎵 RDV Discos</h1>
        <p>Você esqueceu alguns itens especiais no seu carrinho!</p>
    </div>

    <div class="content">
        <p>Olá, <strong>{{ $client->first_name }}</strong>!</p>
        
        <p>Notamos que você deixou alguns vinis incríveis no seu carrinho. Que tal finalizar sua compra antes que alguém leve esses tesouros?</p>

        <h3>Seus itens salvos:</h3>
        
        @foreach($cartItems as $item)
            <div class="cart-item">
                <div class="item-info">
                    <div class="item-title">
                        {{ $item->product->productable->title ?? $item->product->name ?? 'Produto' }}
                    </div>
                    @if($item->product->productable && $item->product->productable->artists && $item->product->productable->artists->count() > 0)
                        <div class="item-artist">
                            {{ $item->product->productable->artists->pluck('name')->join(', ') }}
                        </div>
                    @endif
                    <div class="item-price">
                        R$ {{ number_format($item->product->price ?? 0, 2, ',', '.') }}
                        @if($item->quantity > 1)
                            <span style="color: #6b7280; font-size: 14px;">
                                ({{ $item->quantity }} {{ $item->quantity === 1 ? 'unidade' : 'unidades' }})
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <div class="total">
            Total do Carrinho: R$ {{ number_format($cartTotal, 2, ',', '.') }}
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/carrinho" class="cta-button">
                Finalizar Compra Agora
            </a>
        </div>

        <p style="margin-top: 30px;">
            <strong>Por que escolher a RDV Discos?</strong><br>
            ✅ Vinis originais e de qualidade<br>
            ✅ Entrega segura em todo o Brasil<br>
            ✅ Atendimento especializado<br>
            ✅ Os melhores preços do mercado
        </p>

        <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
            <em>Lembre-se: nosso estoque é limitado e alguns itens podem se esgotar rapidamente!</em>
        </p>
    </div>

    <div class="footer">
        <p>Este email foi enviado porque você tem itens em seu carrinho há mais de 7 dias.</p>
        <p>Se você não deseja mais receber estes lembretes, pode remover os itens do seu carrinho.</p>
        <p>&copy; {{ date('Y') }} RDV Discos. Todos os direitos reservados.</p>
    </div>
</body>
</html>
