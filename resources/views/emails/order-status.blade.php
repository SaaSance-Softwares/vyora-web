@extends('emails.layout')

@section('content')
    @php
        $messages = [
            'confirmed' => "We've received your order and are getting it ready. We'll let you know once it ships!",
            'shipped' => "Great news! Your order is on its way to you.",
            'delivered' => "Your order has been delivered! We hope you love it.",
            'cancelled' => "Your order has been cancelled. If you have any questions, please contact our support.",
            'returned' => "We've received your return.",
        ];
        $headings = [
            'confirmed' => "Order Confirmed!",
            'shipped' => "Order Shipped!",
            'delivered' => "Order Delivered!",
            'cancelled' => "Order Cancelled",
            'returned' => "Return Processed",
        ];
    @endphp

    <h2>{{ $headings[$status] ?? 'Order Update' }}</h2>
    
    <p>{{ $messages[$status] ?? 'There is an update to your order.' }}</p>

    <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 30px 0;">
        <p style="margin: 0; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">Order Summary</p>
        <p style="margin: 5px 0 0 0; font-size: 18px; font-family: monospace; color: #111827;">{{ $order->order_number }}</p>
        <p style="margin: 15px 0 0 0; font-size: 15px; font-weight: bold;">Total: {{ config('app.currency_symbol', '₹') }}{{ number_format($order->total, 2) }}</p>
    </div>
    
    <h3 style="font-size: 16px; margin-bottom: 20px;">Items in your order:</h3>
    
    <div>
        @foreach($order->items as $item)
            <div class="item-row">
                <div class="item-image">
                    @if($item->image)
                        <img src="{{ Str::startsWith($item->image, 'http') ? $item->image : url('storage/' . ltrim($item->image, '/')) }}" alt="{{ $item->product_name ?? 'Product Image' }}">
                    @else
                        <div style="width: 60px; height: 60px; background-color: #e5e7eb; border-radius: 6px;"></div>
                    @endif
                </div>
                <div class="item-details">
                    <p style="margin: 0; font-weight: 600; font-size: 14px; color: #111827;">{{ $item->product_name ?? 'Product' }}</p>
                    <p style="margin: 4px 0 0 0; font-size: 12px; color: #6b7280;">Qty: {{ $item->quantity }}</p>
                </div>
                <div class="item-price">
                    {{ config('app.currency_symbol', '₹') }}{{ number_format($item->price * $item->quantity, 2) }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-center" style="margin-top: 30px;">
        <a href="{{ url('/orders') }}" class="btn">View Order History</a>
    </div>
@endsection
