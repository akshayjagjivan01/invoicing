<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value', 'dimension_type', 'sort_order'];

    /**
     * Get the products in this size.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
