<?php


namespace App\Helpers;

use App\Customer;
use App\FinalInvoice;

class SkladHelper
{
    public function finalInvoice($id)
    {
        $collection = FinalInvoice::where('id', $id)->first();
        return $collection->getAttributes();
    }

    public function customer($id)
    {
        $collection = Customer::where('id', $id)->first();
        return $collection->getAttributes();
    }
}
