<?php

namespace App\Http\Controllers\API;

use App\FinalInvoiceRealm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FinalInvoice;
use DateTime;

class TotalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function totalPerMonth(Request $request)
    {
        $years = $request->years;
        $response = [];
        setlocale(LC_TIME, 'sl_SI.UTF-8');

        for ($i = 1; $i <= 12; $i++) {
            $months[] = ucfirst(strftime('%B', mktime(0, 0, 0, $i)));
        }

        foreach ($years as $year) {
            $priceByMonth = [];
            $total = 0;
            $grandTotal = 0;

            for ($i = 1; $i <= 12; $i++) {
                $month = $i < 10 ? '0'.$i : $i;

                $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $fromDate = date("Y-m-d", strtotime('01-'.$month.'-'.$year));
                $toDate = date("Y-m-d", strtotime($days.'-'.$month.'-'.$year));

                $finalInvoices = $request->realm === env('R1') ?
                    FinalInvoice::whereBetween('timestamp', [$fromDate, $toDate])->get() :
                    FinalInvoiceRealm::whereBetween('timestamp', [$fromDate, $toDate])->get();

                if (empty($finalInvoices)) {
                    $priceByMonth[] = 0;
                } else {
                    foreach ($finalInvoices as $final) {
                        $final = $final->getAttributes();
                        $total += !(boolean)$final['avans'] ? $final['total'] : 0;
                        $grandTotal += !(boolean)$final['avans'] ? $final['total'] : 0;
                    }
                    $priceByMonth[] = $total;
                    $total = 0;
                }
            }

            $toDate = $year < date('Y') ? '31-12-'.$year : date('d-m-Y');

            $yearData = [
                'year' => $year,
                'months' => $months,
                'priceByMonth' => $priceByMonth,
                'interval' => '01-01-'.$year.' ~ '.$toDate,
                'grandTotal' => $grandTotal
            ];

            $response[] = $yearData;
        }

        return response()->json([
            'response' => $response
        ], 200);
    }
}
