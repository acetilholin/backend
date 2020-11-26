<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function recipient()
    {
        return $this->hasOne(Recipient::class);
    }

    public function finalInvoice()
    {
        return $this->hasOne(FinalInvoice::class);
    }
}
