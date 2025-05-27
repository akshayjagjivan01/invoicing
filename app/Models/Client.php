<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'billing_address',
        'shipping_address',
        'contact_person',
        'phone_number',
    ];

    /**
     * Get the user associated with the client.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sales for the client.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
