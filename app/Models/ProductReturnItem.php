<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReturnItem extends Model
{
    protected $fillable = [
        'product_return_id',
        'transaction_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'refund_amount',
        'condition'
    ];

    public function productReturn()
    {
        return $this->belongsTo(ProductReturn::class);
    }

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
