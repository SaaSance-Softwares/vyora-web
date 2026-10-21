<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnRequestController extends Controller
{
        public function create()
    {
        return view('admin.returns.create');
    }

            public function searchOrder(Request $request)
    {
        $q = $request->q;
        if (!$q) {
            return response()->json([]);
        }

        $orders = \App\Models\Order::with([
            'items.product.images', 
            'items.product.skus.size', 
            'items.product.skus.color', 
            'items.sku.color', 
            'items.sku.size', 
            'user', 
            'returnRequests.items'
        ])
            ->where('order_number', 'like', "%{$q}%")
            ->orWhere('id', $q)
            ->limit(10)
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:return,exchange',
            'reason' => 'required|string',
            'comments' => 'nullable|string',
            'fee_deducted' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,approved,received,completed',
            'items' => 'required|array',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.exchange_variant_id' => 'nullable|exists:skus,id'
        ]);

        $order = \App\Models\Order::findOrFail($request->order_id);

        DB::beginTransaction();
        try {
            $returnRequest = new ReturnRequest();
            $returnRequest->uuid = \Illuminate\Support\Str::uuid();
            $returnRequest->order_id = $order->id;
            $returnRequest->user_id = $order->user_id;
            $returnRequest->type = $request->type;
            $returnRequest->reason = $request->reason;
            $returnRequest->comments = $request->comments;
            $returnRequest->status = $request->status;
            
            // Allow admin to waive fees
            if ($request->filled('fee_deducted')) {
                $returnRequest->fee_deducted = $request->fee_deducted;
            } else {
                $returnRequest->fee_deducted = 0;
            }

            // Since it's done by admin, we can auto-approve or set payment status based on what admin chooses
            // For now, if fee > 0 and status is completed, assume paid. Otherwise pending.
            if ($returnRequest->fee_deducted > 0) {
                $returnRequest->payment_status = $request->status === 'completed' ? 'paid' : 'pending';
            }

            if ($request->status === 'approved') $returnRequest->approved_at = now();
            elseif ($request->status === 'received') $returnRequest->received_at = now();
            elseif ($request->status === 'completed') {
                $returnRequest->approved_at = now();
                $returnRequest->received_at = now();
                $returnRequest->completed_at = now();
            }

            $returnRequest->save();

            foreach ($request->items as $itemData) {
                $returnItem = new \App\Models\ReturnRequestItem();
                $returnItem->return_request_id = $returnRequest->id;
                $returnItem->order_item_id = $itemData['order_item_id'];
                $returnItem->quantity = $itemData['quantity'];
                
                if ($request->type === 'exchange' && !empty($itemData['exchange_variant_id'])) {
                    $returnItem->exchange_variant_id = $itemData['exchange_variant_id'];
                }
                
                $returnItem->save();
                
                if ($request->status === 'completed') {
                    $orderItem = \App\Models\OrderItem::find($itemData['order_item_id']);
                    if ($orderItem) {
                        $orderItem->returned_quantity = ($orderItem->returned_quantity ?? 0) + $itemData['quantity'];
                        $orderItem->save();
                        
                        // Restock original item
                        DB::table('skus')->where('id', $orderItem->sku_id)->increment('stock', $itemData['quantity']);
                        
                        // If exchange, deduct new item stock
                        if ($request->type === 'exchange' && !empty($itemData['exchange_variant_id'])) {
                            DB::table('skus')->where('id', $itemData['exchange_variant_id'])->decrement('stock', $itemData['quantity']);
                        }
                    }
                }
            }
            
            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'id' => $returnRequest->id]);
            }
            
            return redirect()->route('admin.returns.show', $returnRequest->id)
                             ->with('success', 'Request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'user']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            })->orWhere('uuid', 'like', "%{$search}%");
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'all' => ReturnRequest::count(),
            'pending' => ReturnRequest::where('status', 'pending')->count(),
            'approved' => ReturnRequest::where('status', 'approved')->count(),
            'received' => ReturnRequest::where('status', 'received')->count(),
            'completed' => ReturnRequest::where('status', 'completed')->count(),
        ];

        return view('admin.returns.index', compact('returns', 'stats'));
    }

    public function show($id)
    {
        $returnRequest = ReturnRequest::with(['order', 'user', 'items.orderItem.product'])->findOrFail($id);
        return view('admin.returns.show', compact('returnRequest'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,received,completed',
            'refund_amount' => 'nullable|numeric|min:0'
        ]);

        $returnRequest = ReturnRequest::with('items.orderItem')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            $oldStatus = $returnRequest->status;
            $returnRequest->status = $request->status;

            if ($request->status !== $oldStatus) {
                if ($request->status === 'approved') $returnRequest->approved_at = now();
                elseif ($request->status === 'rejected') $returnRequest->rejected_at = now();
                elseif ($request->status === 'received') $returnRequest->received_at = now();
                elseif ($request->status === 'completed') $returnRequest->completed_at = now();
            }

            if ($request->status === 'completed' && $oldStatus !== 'completed') {
                // If it's a return, update amount refunded and returned quantities
                if ($returnRequest->type === 'return') {
                    $refundAmount = $request->input('refund_amount', 0);
                    $returnRequest->refund_amount = $refundAmount;
                    
                    $order = $returnRequest->order;
                    $order->amount_refunded = ($order->amount_refunded ?? 0) + $refundAmount;
                    $order->balance_due = max(0, $order->total_amount - $order->amount_paid - $order->amount_refunded);
                    $order->save();

                    // Update returned quantity on items
                    foreach ($returnRequest->items as $item) {
                        $orderItem = $item->orderItem;
                        if ($orderItem) {
                            $orderItem->returned_quantity = ($orderItem->returned_quantity ?? 0) + $item->quantity;
                            $orderItem->save();
                            
                            // Restock inventory
                            DB::table('skus')->where('id', $orderItem->sku_id)->increment('stock', $item->quantity);
                        }
                    }
                }
            }

            $returnRequest->save();

            // Here we would ideally trigger `fireReturnRequestNotifications($returnRequest, $request->status)` 
            // once templates are created, but for now we simply update status.

            DB::commit();
            return redirect()->back()->with('success', 'Return Request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating Return Request: ' . $e->getMessage());
        }
    }
}
