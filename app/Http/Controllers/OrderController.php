<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Log;

class OrderController extends Controller
{
    public function FormData(Request $request)
    {
        try {
            Log::info('FormData request received', [
                'request' => $request->all(),
            ]);
            // Validate the incoming request data
            $data = $request->validate([
                'user_id' => ['required', Rule::exists('users', 'id')],
                'beantrager' => ['required', 'string'],
                'sign' => ['required', 'string'],
                'reuseable_bed_protection' => ['required'],
                'geburtsdatum' => ['required'],
                'versicherter' => ['required', 'array'],
                'versicherter.anrede' => ['required', 'string'],
                'versicherter.titel' => ['nullable', 'string'],
                'versicherter.vorname' => ['required', 'string'],
                'versicherter.nachname' => ['required', 'string'],
                'versicherter.strasse' => ['required', 'string'],
                'versicherter.stadt' => ['required', 'string'],
                'versicherter.plz' => ['required', 'string'],
                'versicherter.land' => ['required', 'string'],
                'versicherter.email' => ['required', 'email'],
                'versicherter.telefon' => ['required', 'string'],
                'address' => ['required', 'array'],
                'address.anrede' => ['required', 'string'],
                'address.titel' => ['nullable', 'string'],
                'address.vorname' => ['required', 'string'],
                'address.nachname' => ['required', 'string'],
                'address.strasse' => ['required', 'string'],
                'address.stadt' => ['required', 'string'],
                'address.plz' => ['required', 'string'],
                'address.land' => ['required', 'string'],
                'address.email' => ['required', 'email'],
                'address.telefon' => ['required', 'string'],
                'antragsteller' => ['required', 'array'],
                'antragsteller.anrede' => ['required', 'string'],
                'antragsteller.titel' => ['nullable', 'string'],
                'antragsteller.vorname' => ['required', 'string'],
                'antragsteller.nachname' => ['required', 'string'],
                'antragsteller.strasse' => ['required', 'string'],
                'antragsteller.stadt' => ['required', 'string'],
                'antragsteller.plz' => ['required', 'string'],
                'antragsteller.land' => ['required', 'string'],
                'antragsteller.email' => ['required', 'email'],
                'antragsteller.telefon' => ['required', 'string'],
                'insuranceType' => ['required', 'string'],
                'insuranceProvider' => ['nullable', 'string'],
                'insuranceNumber' => ['required', 'string'],
                'pflegegrad' => ['required', 'string'],
                'changeProvider' => ['nullable', 'boolean'],
                'requestBedPads' => ['nullable', 'boolean'],
                'deliveryAddress' => ['required', 'string'],
                'applicationReceipt' => ['required', 'string'],
                'awarenessSource' => ['required', 'string'],
                'consultation_check' => ['nullable', 'integer'],
                'products' => ['required', 'array', 'min:1'],
            ]);
        } catch (ValidationException $e) {
            // Log the validation errors
            Log::error('FormData validation failed', [
                'errors' => $e->errors(),
            ]);

            // Optionally, return the validation errors as a response (if needed)
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Prepare the data for saving
        $orderData = [
            'user_id' => $data['user_id'],
            'beantrager' => $data['beantrager'],
            'sign' => $data['sign'],
            'geburtsdatum' => $data['geburtsdatum'],
            'versicherter' => json_encode($data['versicherter']),
            'address' => json_encode($data['address']),
            'antragsteller' => json_encode($data['antragsteller']),
            'insuranceType' => $data['insuranceType'],
            'insuranceProvider' => $data['insuranceProvider'],
            'insuranceNumber' => $data['insuranceNumber'],
            'pflegegrad' => $data['pflegegrad'],
            'changeProvider' => $data['changeProvider'] ?? false,
            'reuseable_bed_protection' => $data['reuseable_bed_protection'] ?? false,
            'deliveryAddress' => $data['deliveryAddress'],
            'applicationReceipt' => $data['applicationReceipt'],
            'awarenessSource' => $data['awarenessSource'],
            'consultation_check' => $data['consultation_check'] ?? 0,
            'products' => $data['products'], // Store the products as a JSON object
        ];

        // Insert or update the order data
        Order::updateOrCreate(
            ['user_id' => $data['user_id']],
            $orderData
        );

        // Return success response
        return response()->json([
            'message' => 'Form saved successfully.',
            'success' => true
        ]);
    }
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

}
