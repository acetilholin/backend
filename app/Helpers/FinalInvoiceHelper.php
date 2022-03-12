<?php


namespace App\Helpers;


use App\FinalInvoice;
use App\FinalInvoiceRealm;
use Illuminate\Support\Facades\DB;

class FinalInvoiceHelper
{
    public function sifraPredracuna($realm)
    {
        $finalInvoices = $realm === env('R1') ?
            FinalInvoice::all() :
            FinalInvoiceRealm::all();

        $max = 0;
        foreach ($finalInvoices as $final) {
            $sifraPredracuna = $final->sifra_predracuna;

            preg_match('/\d{1,4}$/', $sifraPredracuna, $matchYear);

            if ($matchYear[0] === date('Y')) {
                preg_match('/^\d{1,3}/', $sifraPredracuna, $matchSifra);
                $num = $matchSifra[0];
                $max = $num > $max ? $num : $max;
            }
        }
        return ($max + 1).'/'.date("Y");
    }

    public function getAllAndSort($table)
    {
        return DB::select('SELECT * FROM '.$table.' ORDER BY sifra_predracuna + 0 DESC');
    }

    public function finalPerYear($table, $year)
    {
        return DB::select("SELECT * FROM " .$table. " WHERE RIGHT(sifra_predracuna,4) = '".$year."' ORDER BY sifra_predracuna + 0 DESC");
    }


    public function getIntervalAndSort($table, $from, $to)
    {
        return DB::select("SELECT * FROM " .$table. " WHERE timestamp BETWEEN '".$from."' AND '".$to."' ORDER BY sifra_predracuna + 0 ASC");
    }

    public function exportToFinalInvoices($invoiceData, $realm)
    {
        $finalInvoice = $realm === env('R1') ?
            FinalInvoice::where('iid', $invoiceData['iid'])->first() :
            FinalInvoiceRealm::where('iid', $invoiceData['iid'])->first();

        if (!$finalInvoice) {
            $sifraPredracuna = $this->sifraPredracuna($realm);
            $invoiceData['sifra_predracuna'] = $sifraPredracuna;
            if ($realm === env('R1')) {
                FinalInvoice::create($invoiceData);
            } else {
                FinalInvoiceRealm::create($invoiceData);
            }

        } else {
            throw new \Exception(trans('final.alreadyExists'));
        }
    }
}
