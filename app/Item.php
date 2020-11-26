<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'invoice_id', 'qty', 'unit', 'item_price', 'discount', 'total_price', 'description', 'created_at', 'updated_at'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
