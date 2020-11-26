<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];
    protected $table = 'customers';

    public function invoices()
    {
        return $this->hasMany('App\Invoice','customer_id');
    }

    public function finalInvoices()
    {
        return $this->hasMany('App\FinalInvoice','customer_id');
    }
}
