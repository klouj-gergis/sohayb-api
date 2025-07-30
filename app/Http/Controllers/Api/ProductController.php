<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        // Logic to return a list of products
        $products = Product::all();
        return response()->json(ProductResource::collection($products), 200);
    }

    public function show($id)
    {
        // Logic to return a single product by ID
        $product = Product::findOrFail($id);
        return response()->json(new ProductResource($product), 200);
    }

    public function store(Request $request)
    {
        // Logic to create a new product
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validatedData);
        return response()->json(new ProductResource($product), 201);
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing product
        $product = Product::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validatedData);
        return response()->json(new ProductResource($product), 200);
    }

    public function destroy($id)
    {
        // Logic to delete a product
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }
}
