<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'admin_id',
        'status',
        'markup_percentage',
        'invoice_number',
        'invoice_date'
    ];

    /**
     * Get the client associated with this sale.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the admin user who created this sale.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * The products that belong to the sale.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class)
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }

    /**
     * Calculate the total amount for this sale.
     *
     * @return float
     */
    public function calculateTotal()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total += $product->pivot->unit_price * $product->pivot->quantity;
        }

        return $total;
    }
}
