<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sklad extends Model
{
    protected $guarded = [];
    protected  $primaryKey = 'id';

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function finalInvoice()
    {
        return $this->hasOne(FinalInvoice::class);
    }
}
