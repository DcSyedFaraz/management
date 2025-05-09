<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class OrderController extends Controller
{
    public function show(int $userId)
    {
        $order = Order::where('user_id', $userId)->firstOrFail();

        return response()->json([
            'order' => $order,
        ]);
    }
    public function subscriptions(User $user): JsonResponse
    {
        $order = Order::where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        /* ----  shape the payload  ---- */
        $payload = [
            'id' => $order->id,
            'status' => $order->status,                       // active | paused | cancelled
            'lastDispatch' => optional($order->last_dispatch)->toDateString(),
            'residence' => $order->residence,
            'configs' => [
                [
                    'dispatchMonths' => $order->dispatch_months,                 // array
                    'reuseableBedProtection' => (bool) ($order->reuseable_bed_protection ?? false),
                    'products' => $order->products ?? [],
                ],
            ],
        ];

        /* RN code expects a list, so wrap it in an array */
        return response()->json([$payload]);
    }
    /**
     * Create *or* update an order (idempotent “upsert” by user_id).
     * Body:
     *  user_id   : int      (required)
     *  products  : object   (required)  { koala_id : { amount:int } }
     *  dispatch_months & reuseable_bed_protection optional
     */
    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')],
            'products' => ['required', 'array', 'min:1'],
            'products.*.amount' => ['required', 'integer', 'min:1'],
            'dispatch_months' => ['nullable', 'array'],
            'reuseable_bed_protection' => ['nullable', 'boolean'],
        ]);

        $order = Order::updateOrCreate(
            ['user_id' => $data['user_id']],
            [
                'products' => $data['products'],
                'dispatch_months' => $data['dispatch_months'] ?? [],
                'reuseable_bed_protection' => $data['reuseable_bed_protection'] ?? false,
            ]
        );

        return response()->json([
            'message' => 'Order saved successfully.',
            'order' => $order,
        ]);
    }
    public function FormData(Request $request)
    {
        Log::info('FormData', $request->all());

        return response()->json([
            'message' => 'Order saved successfully.',
        ]);
    }
}
