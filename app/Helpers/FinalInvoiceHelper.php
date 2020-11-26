<?php


namespace App\Helpers;


use App\FinalInvoice;
use Illuminate\Support\Facades\DB;

class FinalInvoiceHelper
{
    public function sifraPredracuna()
    {
        $finalInvoices = FinalInvoice::all();

        $max = 0;
        foreach ($finalInvoices as $final) {
            $sifraPredracuna = $final->sifra_predracuna;
            preg_match('/^\d{1,3}/', $sifraPredracuna, $match);
            $num = $match[0];
            $max = $num > $max ? $num : $max;
        }
        return ($max + 1).'/'.date("Y");
    }

    public function getAllAndSort()
    {
        return DB::select('SELECT * FROM final_invoices ORDER BY sifra_predracuna + 0 ASC');
    }

    public function getIntervalAndSort($from, $to)
    {
        return DB::select("SELECT * FROM final_invoices WHERE timestamp BETWEEN '".$from."' AND '".$to."' ORDER BY sifra_predracuna + 0 ASC");
    }

    public function exportToFinalInvoices($invoiceData)
    {
        $finalInvoice = FinalInvoice::where('iid', $invoiceData['iid'])->first();

        if (!$finalInvoice) {
            $sifraPredracuna = $this->sifraPredracuna();
            $invoiceData['sifra_predracuna'] = $sifraPredracuna;
            FinalInvoice::create($invoiceData);
        } else {
            throw new \Exception(trans('final.alreadyExists'));
        }
    }
}
