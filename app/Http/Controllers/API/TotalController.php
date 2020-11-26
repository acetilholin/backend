<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FinalInvoice;
use DateTime;

class TotalController extends Controller
{
    public function totalPerMonth()
    {
        setlocale(LC_TIME, 'sl_SI.UTF-8');
        $month = date('m');

        $currentMonth = substr($month, 0, 1) == '0' ? substr($month, 1, 1) : $month;

        for ($i = 1; $i <= 12; $i++) {
            $months[] = ucfirst(strftime('%B', mktime(0, 0, 0, $i)));
        }

        $year = date('Y');

        $total = 0;
        $grandTotal = 0;

        for ($i = 1; $i <= 12; $i++) {
            $month = $i < 10 ? '0'.$i : $i;
            $fromDate = date("Y-m-d", strtotime('01-'.$month.'-'.$year));
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $toDate = date("Y-m-d", strtotime($days.'-'.$month.'-'.$year));

            $finalInvoices = FinalInvoice::whereBetween('timestamp', [$fromDate, $toDate])->get();

            if (empty($finalInvoices)) {
                $priceByMonth[] = 0;
            } else {
                foreach ($finalInvoices as $final) {
                    $final = $final->getAttributes();
                    $total += $final['total'];
                    $grandTotal += $final['total'];
                }
                $priceByMonth[] = $total;
                $total = 0;
            }
        }

        $toDate = date('d-m-Y');

        return response()->json([
            'months' => $months,
            'priceByMonth' => $priceByMonth,
            'interval' => '01-01-'.$year.' ~ '.$toDate,
            'grandTotal' => $grandTotal
        ], 200);
    }
}
