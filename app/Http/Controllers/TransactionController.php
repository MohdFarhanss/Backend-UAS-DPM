<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function payment(Request $request)
    {
        $request->validate([
            'products'          => 'required|array|min:1',
            'products.*.product_name' => 'required|string|max:50',
            'products.*.price'        => 'required|numeric|min:0',
            'products.*.qty'          => 'required|integer|min:1',
            'payment_method'   => 'required|string|max:20',
        ]);

        $customer = Auth::user();
        if (!$customer) {
            return response()->json(['message' => 'Unauthorized customer.'], 401);
        }

        $trxNumber = 'TRX-' . Str::upper(Str::random(10));
        $transactions = [];
        $grandTotal = 0;

        foreach ($request->products as $product) {
            $totalPrice = $product['price'] * $product['qty'];
            $grandTotal += $totalPrice;

            $transactions[] = Transaction::create([
                'customer_id'    => $customer->id,
                'trx_number'     => $trxNumber,
                'product_name'   => $product['product_name'],
                'price'          => $product['price'],
                'qty'            => $product['qty'],
                'total_price'    => $totalPrice,
                'payment_method' => $request->payment_method,
                'status'         => 'success',
            ]);
        }

        return response()->json([
            'message'       => 'Payment successful.',
            'trx_number'    => $trxNumber,
            'total_amount'  => $grandTotal,
            'transactions'  => $transactions
        ]);
    }


    // Get transaction history for authenticated customer
    public function history()
    {
        $customer = Auth::user();

        if (!$customer) {
            return response()->json(['message' => 'Unauthorized customer.'], 401);
        }

        $transactions = Transaction::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $transactions
        ]);
    }
}
