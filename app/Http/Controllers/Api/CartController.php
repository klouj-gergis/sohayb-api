<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CartResource;
use App\Models\Cart;


class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cartItems = Cart::where('user_id', $user->id)->with('products')->get(); // Assuming user is authenticated
        $total = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
        $cart = [
            'items' => CartResource::collection($cartItems),
            'total' => $total,
        ];
        return response()->json($cart, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Logic to add an item to the cart
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = Auth::user()->carts()->create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);
        return response()->json(new CartResource($cart), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Logic to show a specific cart item
        $cart = Auth::user()->carts()->findOrFail($id);
        return response()->json(new CartResource($cart));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Logic to update a cart item
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $cart = Auth::user()->carts()->findOrFail($id);
        $cart->update([
            'quantity' => $request->quantity,
        ]);
        return response()->json(new CartResource($cart));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Logic to remove an item from the cart
        $cart = Auth::user()->carts()->findOrFail($id);
        $cart->delete();
        return response()->json(null, 204);
    }
}
