<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerRealm extends Model
{
    protected $table = 'customers_2';
    protected $guarded = [];

    public function invoices()
    {
        return $this->hasMany('App\InvoiceRealm','customer_id');
    }

    public function finalInvoices()
    {
        return $this->hasMany('App\FinalInvoiceRealm','customer_id');
    }
}
