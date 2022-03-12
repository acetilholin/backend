<?php


namespace App\Helpers;

use App\Customer;
use App\CustomerRealm;
use App\FinalInvoice;
use App\FinalInvoiceRealm;
use Illuminate\Support\Facades\DB;

class SkladHelper
{
    public function finalInvoice($id, $realm)
    {
        $collection = $realm === env('R1') ?
            FinalInvoice::where('id', $id)->first() :
            FinalInvoiceRealm::where('id', $id)->first();;
        return $collection->getAttributes();
    }

    public function customer($id, $realm)
    {
        $collection = $realm === env('R1') ?
            Customer::where('id', $id)->first() :
            CustomerRealm::where('id', $id)->first();
        return $collection->getAttributes();
    }

    public function getAll($sklads, $realm)
    {
        $all = [];
        foreach ($sklads as $sklad) {
            $all[] = [
                'id' => $sklad['id'],
                'customer' => $this->customer($sklad['customer_id'], $realm),
                'created' => $sklad['created'],
                'work_date' => $sklad['work_date'],
                'item' => $sklad['item'],
                'status' => $sklad['status'],
                'invoice_id' => $this->finalInvoice($sklad['final_invoice_id'], $realm)
            ];
        }
        return $all;
    }

    public function updateSklad($sklad, $id, $table)
    {
        DB::table($table)
            ->where('id', $id)
            ->update([
                'customer_id' => $sklad['customer_id'],
                'final_invoice_id' => $sklad['final_invoice_id'],
                'item' => $sklad['item'],
                'work_date' => $sklad['work_date'],
                'created' => $sklad['created']
            ]);
    }

    public function filterSklads($from, $to, $realm)
    {
        $table = $realm === env('R1') ? 'sklads' : 'sklads_2';
        $all = [];
        $sklads = DB::table($table)
            ->whereBetween('created', [$from, $to])
            ->orderBy('id', 'ASC')
            ->get();

        $helper = new SkladHelper();
        foreach ($sklads as $sklad) {
            $sklad->invoice_id = $helper->finalInvoice($sklad->final_invoice_id, $realm);
            $sklad->customer = $helper->customer($sklad->customer_id, $realm);
            $all[] = $sklad;
        }

        return $all;
    }
}
