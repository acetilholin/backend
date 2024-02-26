<?php


namespace App\Helpers;

use App\FinalInvoice;
use App\Invoice;
use App\InvoiceRealm;
use App\Item;
use App\ItemRealm;
use App\Recipient;
use App\RecipientRealm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceHelper
{
    public function insertData($table, $items)
    {
        foreach ($items as $item) {
            if ($item['id']) {
                DB::table($table)
                    ->where('id', $item['id'])
                    ->update([
                        'qty' => $item['qty'],
                        'unit' => $item['unit'],
                        'item_price' => $item['item_price'],
                        'discount' => $item['discount'],
                        'total_price' => $item['total_price'],
                        'description' => $item['description']
                    ]);
            } else {
                if ($table === 'items') {
                    Item::create($item)->save();
                } else {
                    ItemRealm::create($item)->save();
                }
            }
        }
    }

    public function invoicePerYear($year, $table)
    {
        return DB::select("SELECT * FROM ".$table." WHERE deleted = 0 AND RIGHT(sifra_predracuna,4) = '".$year."' ORDER BY id DESC");
    }

    public function sifraPredracuna($realm)
    {
        $invoices = $realm === env('R1') ? Invoice::all() : InvoiceRealm::all();

        $max = 0;
        foreach ($invoices as $invoice) {
            $sifraPredracuna = $invoice->sifra_predracuna;

            preg_match('/\d{1,4}$/', $sifraPredracuna, $matchYear);

            if ($matchYear[0] === date('Y')) {
                preg_match('/^\d{1,3}/', $sifraPredracuna, $matchSifra);
                $num = $matchSifra[0];
                $max = $num > $max ? $num : $max;
            }
        }
        return ($max + 1).'/'.date("Y");
    }

    public function insertAllData($invoiceData, $recipientData, $items, $realm)
    {
        if ($realm === env('R1')) {
            $invoice = Invoice::create($invoiceData);
        } else {
            $invoice = InvoiceRealm::create($invoiceData);
        }

        $invoice = $invoice->getAttributes();
        $id = $invoice['id'];

        if ($recipientData['title'] !== null && $recipientData['street'] !== null && $recipientData['posta'] !== null) {
            $recipientData['invoice_id'] = $id;
            if ($realm === env('R1')) {
                Recipient::create($recipientData);
            } else {
                RecipientRealm::create($recipientData);
            }
        }

        if ($items) {
            foreach ($items as $item) {
                $item['invoice_id'] = $id;
                if ($realm === env('R1')) {
                    Item::create($item);
                } else {
                    ItemRealm::create($item);
                }
            }
        }
    }

    public function copyInvoice($invoiceData, $items, $realm)
    {
        unset($invoiceData['id']);
        $invoiceData['sifra_predracuna'] = $this->sifraPredracuna($realm);
        $invoiceData['iid'] = Str::uuid();
        $invoice = $realm === env('R1') ? Invoice::create($invoiceData) : InvoiceRealm::create($invoiceData);
        $invoice = $invoice->getAttributes();
        $id = $invoice['id'];

        if ($items) {
            foreach ($items as $item) {
                $data = $item->getAttributes();
                unset($data['id']);
                $data['invoice_id'] = $id;
                if ($realm === env('R1')) {
                    Item::create($data);
                } else {
                    ItemRealm::create($data);
                }
            }
        }
    }
}
