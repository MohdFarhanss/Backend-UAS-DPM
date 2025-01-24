<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function favorite($productId)
    {
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // Check if the product is already in the favorites
        if ($customer->favoriteProducts->contains('id', $productId)) {
            return response()->json(['message' => 'Product is already in favorites!']);
        }

        // Attach product to customer as a favorite
        $customer->favoriteProducts()->attach($productId);

        return response()->json(['message' => 'Product added to favorites!']);
    }


    // Mark a product as unfavorite
    public function unfavorite($productId)
    {
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        // Find the product by ID
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // Detach the product from the customer's favoriteProducts
        $customer->favoriteProducts()->detach($productId);

        return response()->json(['message' => 'Product removed from favorites!']);
    }


    // Get the list of favorite products for the customer
    public function getFavorites()
    {
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        $favorites = $customer->favoriteProducts;

        return response()->json(['favorites' => $favorites]);
    }

}
