<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);
        // Implement checkout logic here
        $user = $request->user();
        $cartItems = $user->carts()->with('product')->get();
        $subtotal = 0;
        $items = [];
        foreach ($cartItems as $item) {
            $product = $item->product;
            if($product->stock < $item->quantity) {
                return response()->json(['message' => "not enogh stock to {$product->name}"]);
            }
            $itemSubtotal = round($product->price * $item->quantity, 2);
            $subtotal += $itemSubtotal;

            $items[] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $product->price,
                'subtotal' => $itemSubtotal,
            ];
        };

        $tax = round($subtotal * 0.1, 2); // Assuming a 10% tax rate
        $shippingCost = 5.00; // Flat shipping cost
        $total = round($subtotal + $tax + $shippingCost, 2);

        DB::beginTransaction();
        try{
            $order = $user->orders()->create([
                'status' => OrderStatus::PENDING,
                'shipping_name' => $data['shipping_name'],
                'shipping_phone' => $data['shipping_phone'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'shipping_address' => $data['shipping_address'],
                'payment_method' => $data['payment_method'] ?? 'cod',
                'payment_status' => PaymentStatus::PENDING,
                'order_number' => uniqid('order_'),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            // Update product stock
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $product->stock -= $cartItem->quantity;
                $product->save();
            }

            // Clear the cart after successful checkout
            Cart::where('user_id', $user->id)->each(function ($cartItem) {
                $cartItem->delete();
            });

            DB::commit();
            return response()->json([
                'message' => 'Order placed successfully',
                'order' => new OrderResource($order->load('items')),
                'status' => 'success',
            ], 201);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
        // This could include validating the request, processing payment, etc.

        return response()->json(['message' => 'Order placed successfully'], 200);
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
