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
            'reuseable_bed_protection' => ['nullable', 'boolean'],
            'beantrager' => ['nullable', 'string'],
            'sign' => ['nullable', 'string'],
            'geburtsdatum' => ['nullable', 'string'],
            'versicherter' => ['nullable'],
            'address' => ['nullable'],
            'antragsteller' => ['nullable'],
            'insuranceType' => ['nullable', 'string'],
            'insuranceProvider' => ['nullable', 'string'],
            'insuranceNumber' => ['nullable', 'string'],
            'pflegegrad' => ['nullable', 'string'],
            'changeProvider' => ['nullable', 'boolean'],
            'requestBedPads' => ['nullable', 'boolean'],
            'isSameAsContact' => ['nullable', 'boolean'],
            'deliveryAddress' => ['nullable', 'string'],
            'applicationReceipt' => ['nullable', 'string'],
            'awarenessSource' => ['nullable', 'string'],
            'consultation_check' => ['nullable', 'integer'],
            'products' => ['nullable'],
            'dispatch_months' => ['nullable'],
        ]);

        $data['changeProvider'] = $request->boolean('changeProvider');
        $data['requestBedPads'] = $request->boolean('requestBedPads');
        $data['reuseable_bed_protection'] = $request->boolean('reuseable_bed_protection');
        $data['isSameAsContact'] = $request->boolean('isSameAsContact');

        foreach (['versicherter', 'address', 'antragsteller', 'products', 'dispatch_months'] as $jsonField) {
            if ($request->filled($jsonField)) {
                $data[$jsonField] = json_decode($request->input($jsonField), true) ?? [];
            }
        }

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

