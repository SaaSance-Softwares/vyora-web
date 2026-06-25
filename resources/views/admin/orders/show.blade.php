@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)
@section('content')
@php
$statusColors = ['pending'=>'bg-amber-100 text-amber-700','processing'=>'bg-blue-100 text-blue-700','shipped'=>'bg-indigo-100 text-indigo-700','delivered'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-rose-100 text-rose-700','refunded'=>'bg-gray-100 text-gray-600'];
$sc = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600';
$payClass = $order->payment_status==='paid' ? 'text-emerald-600' : 'text-amber-600';
$billing = $order->billingAddress;
$shipping = $order->shippingAddress;
@endphp

<div class="space-y-6">
{{-- HEADER --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div class="flex items-center gap-3">
    <a href="{{ route('admin.orders.index') }}" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
      <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h1 class="text-xl font-bold text-gray-900">{{ $order->order_number }}</h1>
      <p class="text-sm text-gray-500">Placed {{ $order->created_at->format('d M Y, h:i A') }}</p>
    </div>
  </div>
  <div class="flex items-center gap-3">
    <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $sc }}">{{ $order->status_label }}</span>
    <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $payClass }} bg-gray-50 border">{{ $order->payment_status }}</span>
  </div>
</div>

@if(session('success'))
<div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium">
  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
  {{ session('success') }}
</div>
@endif

{{-- TIMELINE --}}
<div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
  <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-6">Order Timeline</h2>
  <div class="flex items-start gap-0">
    @foreach($order->timeline as $i => $step)
    @php $isLast = $i === count($order->timeline)-1; @endphp
    <div class="flex-1 flex flex-col items-center {{ $isLast ? '' : '' }}">
      <div class="flex items-center w-full">
        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10
          {{ $step['current'] ? 'bg-gray-900 text-white ring-4 ring-gray-900/10' : ($step['done'] ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-400') }}">
          @if($step['done'] && !$step['current'])
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          @else
            <span class="text-xs font-bold">{{ $i+1 }}</span>
          @endif
        </div>
        @if(!$isLast)
          <div class="flex-1 h-0.5 {{ $step['done'] ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
        @endif
      </div>
      <div class="text-center mt-2 px-1">
        <p class="text-xs font-bold {{ $step['current'] ? 'text-gray-900' : ($step['done'] ? 'text-emerald-600' : 'text-gray-400') }}">{{ $step['label'] }}</p>
        @if($step['key']==='shipped' && $order->shipped_at)
          <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->shipped_at->format('d M Y') }}</p>
        @elseif($step['key']==='delivered' && $order->delivered_at)
          <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->delivered_at->format('d M Y') }}</p>
        @elseif($step['key']==='pending')
          <p class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
        @endif
      </div>
    </div>
    @endforeach
  </div>
</div>

{{-- MAIN GRID --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  {{-- LEFT: Items + Financials --}}
  <div class="lg:col-span-2 space-y-6">

    {{-- ITEMS --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
        <h2 class="font-bold text-gray-900">Order Items</h2>
        <span class="text-sm text-gray-500">{{ $order->items->count() }} {{ Str::plural('item',$order->items->count()) }}</span>
      </div>
      <div class="divide-y divide-gray-50">
        @foreach($order->items as $item)
        @php
          $img = $item->image_url;
          if(!$img && $item->product) {
            $primary = $item->product->images->where('is_primary',true)->first() ?? $item->product->images->first();
            $img = $primary ? asset('storage/'.$primary->path) : null;
          }
          $attrs = $item->sku?->attributeValues ?? collect();
        @endphp
        <div class="flex gap-4 p-5 hover:bg-gray-50/50 transition-colors">
          {{-- Image --}}
          <div class="w-16 h-20 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shrink-0">
            @if($img)
              <img src="{{ $img }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
            @else
              <div class="w-full h-full flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
            @endif
          </div>
          {{-- Details --}}
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ $item->product_name }}</p>
            @if($item->variant_name)
              <p class="text-xs text-gray-500 mt-0.5">{{ $item->variant_name }}</p>
            @endif
            @if($attrs->count())
              <div class="flex flex-wrap gap-1 mt-1">
                @foreach($attrs as $av)
                  <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-semibold">{{ $av->attribute->name }}: {{ $av->value }}</span>
                @endforeach
              </div>
            @endif
            @if($item->sku)
              <p class="text-[10px] text-gray-400 mt-1 font-mono">SKU: {{ $item->sku->code }}</p>
            @endif
          </div>
          {{-- Price --}}
          <div class="text-right shrink-0">
            <p class="text-sm text-gray-500">{{ $item->quantity }} × ₹{{ number_format($item->price) }}</p>
            <p class="font-black text-gray-900 mt-1">₹{{ number_format($item->total) }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- FINANCIALS --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <h2 class="font-bold text-gray-900 mb-4">Financial Summary</h2>
      <div class="space-y-2.5">
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Subtotal</span>
          <span class="font-semibold">₹{{ number_format($order->subtotal) }}</span>
        </div>
        @if($order->shipping_amount > 0)
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Shipping</span>
          <span class="font-semibold">₹{{ number_format($order->shipping_amount) }}</span>
        </div>
        @endif
        @php
          $taxBreakdown = json_decode($order->tax_breakdown ?? '{}', true);
          $isTaxIncluded = abs(($order->total_amount + $order->discount_amount) - ($order->subtotal + $order->shipping_amount)) < 0.1;
        @endphp
        @if(!empty($taxBreakdown))
          @foreach($taxBreakdown as $rate => $amount)
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Tax @ {{ $rate }}% {{ $isTaxIncluded ? '(Included)' : '' }}</span>
            <span class="font-semibold">₹{{ number_format($amount, 2) }}</span>
          </div>
          @endforeach
        @elseif($order->tax_amount > 0)
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Tax</span>
          <span class="font-semibold">₹{{ number_format($order->tax_amount) }}</span>
        </div>
        @endif
        @if($order->discount_amount > 0)
        <div class="flex justify-between text-sm text-emerald-600">
          <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
          <span class="font-semibold">-₹{{ number_format($order->discount_amount) }}</span>
        </div>
        @endif
        <div class="pt-3 mt-2 border-t border-gray-100 flex justify-between">
          <span class="font-black text-gray-900">Grand Total</span>
          <span class="font-black text-xl text-gray-900">₹{{ number_format($order->total_amount) }}</span>
        </div>
      </div>
      <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
        <div>
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Payment Method</p>
          <p class="font-semibold text-gray-800">{{ $order->payment_method ?? '—' }}</p>
        </div>
        <div>
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Payment Status</p>
          <p class="font-bold {{ $payClass }}">{{ strtoupper($order->payment_status) }}</p>
        </div>
        @if($order->transaction_id)
        <div class="col-span-2">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Transaction ID</p>
          <p class="font-mono text-sm text-gray-700 break-all">{{ $order->transaction_id }}</p>
        </div>
        @endif
      </div>
    </div>

    {{-- NOTES --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <h2 class="font-bold text-gray-900 mb-3">Order Notes</h2>
      <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="{{ $order->status }}">
        <textarea name="notes" rows="3" placeholder="Add internal notes about this order..."
          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">{{ $order->notes }}</textarea>
        <button type="submit" class="mt-2 px-4 py-2 bg-gray-900 text-white rounded-xl text-xs font-semibold hover:bg-gray-700 transition-colors">Save Notes</button>
      </form>
    </div>

  </div>

  {{-- RIGHT SIDEBAR --}}
  <div class="space-y-6">

    {{-- STATUS UPDATE --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Update Order</h2>
      <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" id="statusForm">
        @csrf @method('PATCH')
        <div class="space-y-3">
          <div>
            <label class="text-xs font-semibold text-gray-600 mb-1 block">Order Status</label>
            <select name="status" id="statusSelect" onchange="toggleTracking(this.value)"
              class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white">
              @foreach(['pending','processing','shipped','delivered','cancelled','refunded'] as $s)
                <option value="{{ $s }}" {{ $order->status===$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
              @endforeach
            </select>
          </div>

          {{-- Tracking fields (shown when shipped) --}}
          <div id="trackingFields" class="{{ $order->status==='shipped' ? '' : 'hidden' }} space-y-3 pt-2 border-t border-gray-100">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">🚚 Shipping Details</p>
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Courier Partner</label>
              <input type="text" name="courier_partner" value="{{ $order->courier_partner }}" placeholder="e.g. Delhivery, BlueDart"
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Tracking Number</label>
              <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="AWB / Tracking No."
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600 mb-1 block">Tracking URL <span class="text-gray-400 font-normal">(shared with customer)</span></label>
              <input type="url" name="tracking_url" value="{{ $order->tracking_url }}" placeholder="https://track.courier.com/..."
                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <p class="text-[10px] text-gray-400">Customer will receive Email, SMS & WhatsApp with the tracking link.</p>
          </div>

          <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-xl text-sm font-bold hover:bg-gray-700 transition-colors">
            Save Changes
          </button>
        </div>
      </form>
    </div>

    {{-- SHIPROCKET --}}
    @if(\App\Models\ThemeSetting::where('group', 'integration.shiprocket')->where('key', 'enabled')->value('value') == '1')
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-[#7367F0]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Shiprocket</h2>
      </div>

      @if($order->shiprocket_order_id)
        <div class="space-y-2">
          <p class="text-xs text-gray-500"><span class="font-bold text-gray-700">Order ID:</span> {{ $order->shiprocket_order_id }}</p>
          @if($order->shiprocket_shipment_id)
            <p class="text-xs text-gray-500"><span class="font-bold text-gray-700">Shipment ID:</span> {{ $order->shiprocket_shipment_id }}</p>
          @endif
          <a href="https://app.shiprocket.in/orders" target="_blank" class="block w-full text-center mt-3 py-2 bg-gray-50 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-100 transition-colors border border-gray-200">
            View in Shiprocket
          </a>
        </div>
      @else
        <form action="{{ route('admin.orders.shiprocket', $order) }}" method="POST">
          @csrf
          <button type="submit" class="w-full py-2.5 bg-[#7367F0] text-white rounded-xl text-sm font-bold hover:bg-[#5E50EE] transition-colors flex items-center justify-center gap-2">
            Send to Shiprocket
          </button>
        </form>
        <p class="text-[10px] text-gray-400 mt-2 text-center">Creates an ad-hoc order in your Shiprocket account.</p>
      @endif
    </div>
    @endif

    {{-- TRACKING DISPLAY (when already shipped) --}}
    @if($order->tracking_url || $order->tracking_number)
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        <h3 class="text-sm font-bold text-indigo-800">Tracking Info</h3>
      </div>
      @if($order->courier_partner)
        <p class="text-xs text-indigo-700 mb-1"><span class="font-bold">Courier:</span> {{ $order->courier_partner }}</p>
      @endif
      @if($order->tracking_number)
        <p class="text-xs text-indigo-700 mb-2"><span class="font-bold">AWB:</span> <span class="font-mono">{{ $order->tracking_number }}</span></p>
      @endif
      @if($order->tracking_url)
        <a href="{{ $order->tracking_url }}" target="_blank"
           class="inline-flex items-center gap-2 w-full justify-center py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          Open Tracking Page
        </a>
      @endif
    </div>
    @endif

    {{-- CUSTOMER --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Customer</h2>
      @if($order->user)
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
          <div class="w-10 h-10 bg-gray-900 text-white rounded-full flex items-center justify-center font-bold text-sm">
            {{ strtoupper(substr($order->user->name,0,1)) }}
          </div>
          <div>
            <p class="font-semibold text-gray-900">{{ $order->user->name }}</p>
            <p class="text-xs text-gray-400">Customer Account</p>
          </div>
        </div>
      @endif
      @if($shipping)
        <div class="space-y-1.5 text-sm">
          <div class="font-semibold text-gray-900">{{ $shipping->name }}</div>
          <a href="mailto:{{ $shipping->email }}" class="text-blue-600 hover:underline text-xs block">{{ $shipping->email }}</a>
          <a href="tel:{{ $shipping->phone }}" class="text-gray-600 text-xs block">{{ $shipping->phone }}</a>
        </div>
      @endif
    </div>

    {{-- SHIPPING ADDRESS --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Shipping Address</h2>
      @if($shipping)
        <address class="text-sm text-gray-700 not-italic leading-relaxed">
          <span class="font-semibold">{{ $shipping->name }}</span><br>
          {{ $shipping->address_line1 }}<br>
          @if($shipping->address_line2) {{ $shipping->address_line2 }}<br> @endif
          {{ $shipping->city }}, {{ $shipping->state }} — {{ $shipping->zip_code }}<br>
          <span class="text-gray-500">India</span>
        </address>
      @else
        <p class="text-sm text-gray-400">No address on file.</p>
      @endif
    </div>

    {{-- BILLING ADDRESS --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
      <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">Billing Address</h2>
      @if($billing && $billing->id !== $shipping?->id)
        <address class="text-sm text-gray-700 not-italic leading-relaxed">
          <span class="font-semibold">{{ $billing->name }}</span><br>
          {{ $billing->address_line1 }}<br>
          @if($billing->address_line2) {{ $billing->address_line2 }}<br> @endif
          {{ $billing->city }}, {{ $billing->state }} — {{ $billing->zip_code }}
        </address>
      @else
        <p class="text-sm text-gray-500 italic">Same as shipping address.</p>
      @endif
    </div>

  </div>
</div>
</div>

<script>
function toggleTracking(status) {
  const fields = document.getElementById('trackingFields');
  if (status === 'shipped') {
    fields.classList.remove('hidden');
  } else {
    fields.classList.add('hidden');
  }
}
</script>
@endsection
