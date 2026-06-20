<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = User::where(function ($q) {
            $q->whereIn('role', ['customer', 'user'])->orWhereNull('role')->orWhere('role', '');
        })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Display the specified customer details.
     */
    public function show($id)
    {
        $customer = User::with([
            'orders' => function ($query) {
                $query->latest();
            },
            'addresses',
            'orders.items',
        ])->findOrFail($id);

        $customer->total_spent = $customer->orders->sum('total_amount');
        $customer->average_order_value = $customer->orders->count() > 0
            ? round($customer->total_spent / $customer->orders->count(), 2)
            : 0;

        return view('admin.customers.show', [
            'customer' => $customer,
        ]);
    }
}
