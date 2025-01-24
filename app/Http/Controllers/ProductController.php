<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        // Get the authenticated customer using the token
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        $categoryId = $request->query('category_id');

        if ($categoryId) {
            $products = Product::where('category_id', $categoryId)->get();
        } else {
            $products = Product::all();
        }

        // Attach favorite status to each product
        $products = $products->map(function ($product) use ($customer) {
            // Check if the product is favorited by the customer
            $product->is_favorited = $customer->favoriteProducts->contains($product->id);
            return $product;
        });

        return response()->json([
            'data' => $products,
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }
}
