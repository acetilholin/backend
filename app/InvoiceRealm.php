<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceRealm extends Model
{
    protected $table = 'invoices_2';
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(ItemRealm::class, 'invoice_id');
    }

    public function recipient()
    {
        return $this->hasOne(RecipientRealm::class, 'invoice_id');
    }

    public function finalInvoice()
    {
        return $this->hasOne(FinalInvoiceRealm::class,'sifra_predracuna');
    }
}
