<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'group', 'sort_order'];

    /**
     * Get the products with this specification.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('value')
                    ->withTimestamps();
    }
}
