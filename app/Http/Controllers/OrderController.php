<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function show(int $userId)
    {
        $order = Order::where('user_id', $userId)->firstOrFail();

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Create *or* update an order (idempotent “upsert” by user_id).
     * Body:
     *  user_id   : int      (required)
     *  products  : object   (required)  { koala_id : { amount:int } }
     *  dispatch_months & reusable_bed_protection optional
     */
    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')],
            'products' => ['required', 'array', 'min:1'],
            'products.*.amount' => ['required', 'integer', 'min:1'],
            'dispatch_months' => ['nullable', 'array'],
            'reusable_bed_protection' => ['nullable', 'boolean'],
        ]);

        $order = Order::updateOrCreate(
            ['user_id' => $data['user_id']],
            [
                'products' => $data['products'],
                'dispatch_months' => $data['dispatch_months'] ?? [],
                'reusable_bed_protection' => $data['reusable_bed_protection'] ?? false,
            ]
        );

        return response()->json([
            'message' => 'Order saved successfully.',
            'order' => $order,
        ]);
    }
}
