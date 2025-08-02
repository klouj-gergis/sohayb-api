<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;

class CheckoutController extends Controller
{

    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            // 1. Validate cart and calculate all prices
            $cartData = $this->orderService->validateCart($user); // contains cartItems, subtotal, tax, shippingCost, total

            // 2. Create order and related items
            $order = $this->orderService->createOrder(
                $user,
                $data,
                $cartData['cartItems'],
                $cartData['total'],
                $cartData['subTotal'],
                $cartData['tax'],
                $cartData['shippingCost']
            );

            // 3. Optionally: integrate Paymob here if needed

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => new OrderResource($order->load('items')),
                'status' => 'success',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orderHistory(Request $request)
    {
        // Logic to retrieve order history
        $user = $request->user();
        $orders = $user->orders()->with('items')->get();
        return response()->json(OrderResource::collection($orders), 200);
    }
    public function orderDetails($orderId, Request $request)
    {
        // Logic to retrieve details of a specific order
        $user = $request->user();
        $order = $user->orders()->with('items')->findOrFail($orderId);
        if(!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json(new OrderResource($order), 200);
    }
}
