<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemRealm extends Model
{
    protected $table = 'items_2';
    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(InvoiceRealm::class);
    }
}
