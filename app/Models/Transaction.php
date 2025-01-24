<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'trx_number',
        'product_name',
        'price',
        'qty',
        'total_price',
        'payment_method',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
