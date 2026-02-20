<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    protected $fillable = [
        'return_number',
        'transaction_id',
        'user_id',
        'reason',
        'status',
        'refund_amount',
        'refund_method',
        'notes'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ProductReturnItem::class);
    }
}
