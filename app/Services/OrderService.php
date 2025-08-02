<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cart;
use App\Enums\OrderStatus;


class OrderService
{
    public function validateCart(User $user){
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
        if ($cartItems->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        $subTotal = 0;
        $total = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            if(!$product) {
                throw new \Exception("Product not found for cart item ID: {$item->id}");
            }

            if ($item->product->stock < $item->quantity) {
                throw new \Exception("Not enough stock for product: {$product->name}");
            }

            $subTotal += $item->product->price * $item->quantity;
            

        }

        $tax = round($subTotal * config('commerce.tax_rate', 0.2), 2);
        $$shippingThreshold = config('commerce.free_shipping_min', 500.00);
        $shippingCost = $subTotal >= $$shippingThreshold ? 0 : config('commerce.shipping_cost', 50.00);
        $total = $subTotal + $tax + $shippingCost;

        return [
            'cartItems' => $cartItems,
            'total' => $total,
            'subTotal' => $subTotal,
            'tax' => $tax,
            'shippingCost' => $shippingCost,
        ];

    }

    public function createOrder(User $user, array $data, $cartItems, $total, $subTotal, $tax, $shippingCost)
    {
        $order = $user->orders()->create([
            'status' => OrderStatus::PENDING,
            'shipping_name' => $data['shipping_name'],
            'shipping_phone' => $data['shipping_phone'],
            'shipping_address' => $data['shipping_address'],
            'subtotal' => $subTotal,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'payment_method' => $data['payment_method'] ?? 'cod',
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
                'subtotal' => round($item->product->price * $item->quantity, 2),
            ]);
        }
        Cart::where('user_id', $user->id)->delete();

        return $order;
    }
}   