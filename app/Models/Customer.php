<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory;
    use HasApiTokens, Notifiable;

    protected $fillable =  ['username','email','gender','birthday','phone','otp_code','otp_expires_at'];

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites');
    }

}
