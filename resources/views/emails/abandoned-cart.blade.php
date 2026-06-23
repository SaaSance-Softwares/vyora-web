<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Did you forget something?</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9f9f9; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; margin-top: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; margin: 0; color: #111; }
        .content { font-size: 16px; line-height: 1.5; color: #555; }
        .products { margin: 30px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 20px 0; }
        .product { display: flex; align-items: center; margin-bottom: 15px; }
        .product:last-child { margin-bottom: 0; }
        .product img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; margin-right: 15px; background: #f0f0f0; }
        .product-info { flex: 1; }
        .product-name { font-weight: bold; font-size: 15px; margin: 0 0 5px 0; color: #222; }
        .product-meta { font-size: 13px; color: #888; margin: 0; }
        .product-price { font-weight: bold; color: #111; }
        .btn-container { text-align: center; margin: 40px 0; }
        .btn { display: inline-block; background-color: #000; color: #fff; text-decoration: none; padding: 14px 30px; border-radius: 4px; font-weight: bold; font-size: 16px; }
        .footer { text-align: center; font-size: 12px; color: #aaa; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Looks like you forgot something!</h1>
        </div>
        <div class="content">
            <p>Hi there,</p>
            <p>We noticed you left some great items in your cart. They are still waiting for you, but we can't guarantee they will stay in stock forever!</p>
            
            <div class="products">
                @foreach($cart->items as $item)
                <div class="product">
                    @if($item->image)
                        <img src="{{ $item->image }}" alt="Product Image">
                    @else
                        <div style="width: 60px; height: 60px; background: #eee; border-radius: 4px; margin-right: 15px;"></div>
                    @endif
                    <div class="product-info">
                        <p class="product-name">{{ $item->sku->product->name ?? 'Product' }}</p>
                        <p class="product-meta">Qty: {{ $item->quantity }}</p>
                    </div>
                    <div class="product-price">
                        ₹{{ number_format($item->price, 2) }}
                    </div>
                </div>
                @endforeach
            </div>

            <div class="btn-container">
                <a href="{{ $checkoutUrl }}" class="btn">Return to Checkout</a>
            </div>
            
            <p style="text-align: center; font-size: 14px;">If you have any questions or need help, just reply to this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
