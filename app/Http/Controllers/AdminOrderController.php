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
            'geburtsdatum' => ['nullable', 'date'],

            // JSON groups as arrays
            'versicherter' => ['nullable', 'array'],
            'versicherter.anrede' => ['nullable', 'string'],
            'versicherter.titel' => ['nullable', 'string'],
            'versicherter.vorname' => ['nullable', 'string'],
            'versicherter.nachname' => ['nullable', 'string'],
            'versicherter.strasse' => ['nullable', 'string'],
            'versicherter.stadt' => ['nullable', 'string'],
            'versicherter.plz' => ['nullable', 'string'],
            'versicherter.land' => ['nullable', 'string'],
            'versicherter.email' => ['nullable', 'email'],
            'versicherter.telefon' => ['nullable', 'string'],

            'address' => ['nullable', 'array'],
            'address.*' => ['nullable', 'string'],

            'antragsteller' => ['nullable', 'array'],
            'antragsteller.*' => ['nullable', 'string'],

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

            'product_ids' => 'required|array',
            'product_ids.*' => 'required|string',
            'product_amounts' => 'required|array',
            'product_amounts.*' => 'required|integer|min:1',

            'dispatch_months' => ['nullable', 'array'],
            'dispatch_months.*' => ['integer', 'between:1,12'],
        ]);

        $products = [];
        foreach ($data['product_ids'] as $i => $id) {
            $products[$id] = ['amount' => $data['product_amounts'][$i]];
        }
        $data['products'] = $products;
        // dd($data['products']);
        unset($data['product_ids']);
        unset($data['product_amounts']);

        // Cast booleans explicitly
        $data['changeProvider'] = $request->boolean('changeProvider');
        $data['requestBedPads'] = $request->boolean('requestBedPads');
        $data['reuseable_bed_protection'] = $request->boolean('reuseable_bed_protection');
        $data['isSameAsContact'] = $request->boolean('isSameAsContact');

        // No manual JSON decoding needed: Laravel model casts will handle arrays -> JSON
        $order->update($data);

        return redirect()
            ->route('orders.edit', $order->id)
            ->with('success', 'Order updated successfully');
    }

    public function detachConnectedUser(Order $order, User $user)
    {
        $order->user->connectedUsers()->detach($user->id);
        return back()->with('success', 'Connected user delinked');
    }
}

