<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'sku',
        'is_active',
        'brand_id',
        'size_id',
        'image_path',
        'attributes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set created_by and updated_by fields when creating/updating products
        static::creating(function ($product) {
            if (Auth::check()) {
                $product->created_by = Auth::id();
                $product->updated_by = Auth::id();
            }
        });

        static::updating(function ($product) {
            if (Auth::check()) {
                $product->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get the user who created this product.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this product.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the brand of the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the size of the product.
     */
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get the specifications for the product.
     */
    public function specifications()
    {
        return $this->belongsToMany(Specification::class)
                    ->withPivot('value')
                    ->withTimestamps();
    }

    /**
     * Get the categories for the product.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get the sales that include this product.
     */
    public function sales()
    {
        return $this->belongsToMany(Sale::class)
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }
}
