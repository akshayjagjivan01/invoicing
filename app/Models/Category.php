<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'parent_id', 'sort_order', 'is_active'];

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the subcategories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Get all products in this category and its subcategories.
     */
    public function allProducts()
    {
        $descendantIds = $this->descendants()->pluck('id')->push($this->id);

        return Product::whereHas('categories', function ($query) use ($descendantIds) {
            $query->whereIn('categories.id', $descendantIds);
        });
    }

    /**
     * Get all descendant categories (recursive).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }
}
