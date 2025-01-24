<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable =  [
        'category_id',
        'title',
        'sub_title',
        'desc',
        'price',
        'img',
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(Customer::class, 'favorites');
    }
}
