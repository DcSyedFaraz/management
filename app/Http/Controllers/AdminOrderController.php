<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->get();
        return view('orders.index', compact('orders'));
    }

    public function edit(Order $order)
    {
        $order->load('user');
        $connectedUsers = $order->user->connectedUsers()->with('userDetail')->get();
        return view('orders.edit', compact('order', 'connectedUsers'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['nullable', 'string'],
            'last_dispatch' => ['nullable', 'date'],
            'residence' => ['nullable', 'string'],
        ]);
        $order->update($data);

        return redirect()->route('orders.edit', $order->id)
            ->with('success', 'Order updated successfully');
    }

    public function detachConnectedUser(Order $order, User $user)
    {
        $order->user->connectedUsers()->detach($user->id);
        return back()->with('success', 'Connected user delinked');
    }
}

