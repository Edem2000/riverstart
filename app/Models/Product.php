<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'name', 'description', 'price', 'published', 'deleted'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_products');
    }

}
