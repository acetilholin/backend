<?php


namespace App\Helpers;

use App\Customer;
use App\Invoice;
use Illuminate\Support\Facades\Log;

class SkladHelper
{
    public function invoice($id)
    {
        $collection = Invoice::where('id', $id)->first();
        return $collection->getAttributes();
    }

    public function customer($id)
    {
        $collection = Customer::where('id', $id)->first();
        return $collection->getAttributes();
    }
}
