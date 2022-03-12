<?php


namespace App\Helpers;

use App\Day;
use App\DayRealm;
use App\Employee;
use App\Month;
use App\MonthRealm;
use Illuminate\Support\Facades\DB;

class MonthHelper
{
    public function insert($daysData, $reportData, $realm)
    {
        $monthNo = $reportData['month_no'];
        $date = date("Y").'-'.$monthNo.'-01';
        $reportData['date'] = $date;
        if ($realm === env('R1')) {
            $month = Month::create($reportData);
        } else {
            $month = MonthRealm::create($reportData);
        }

        $month = $month->getAttributes();
        $month_id = $month['id'];

        if ($daysData) {
            foreach ($daysData as $days) {
                unset($days['id']);
                $days['month_id'] = $month_id;
                if ($realm === env('R1')) {
                    Day::create($days);
                } else {
                    DayRealm::create($days);
                }
            }
        }
    }

    public function filter($from, $to, $employee_id, $realm)
    {
        $monthsTable = $realm === env('R1') ? 'months' : 'months_2';

        if ($employee_id === 0) {
            $months = DB::table($monthsTable)
                ->whereBetween('date', [$from, $to])
                ->orderBy('date', 'asc')
                ->get();
        } else {
            $months = DB::table($monthsTable)
                ->whereBetween('date', [$from, $to])
                ->where('employee_id', $employee_id)
                ->orderBy('date', 'asc')
                ->get();
        }

        return $months;
    }

    public function employee($id)
    {
        $employeeData = Employee::find($id);
        $employee = $employeeData->getAttributes();
        return $employee['person'];
    }

    public function getDays($mid, $realm)
    {
        $allDays = [];
        $days = $realm === env('R1') ?
            Day::where('month_id', $mid)->get() :
            DayRealm::where('month_id', $mid)->get();

        foreach ($days as $day) {
            $allDays[] = $day->getAttributes();
        }
        return $allDays;
    }

    public function copy($month, $days, $realm)
    {
        unset($month['id']);
        $month = $realm === env('R1') ? Month::create($month) : MonthRealm::create($month);

        if ($days) {
            foreach ($days as $day) {
                $day = $day->getAttributes();
                $day['month_id'] = $month['id'];
                if ($realm === env('R1')) {
                    Day::create($day);
                } else {
                    DayRealm::create($day);
                }
            }
        }
    }

    public function update($days, $realm)
    {
        foreach ($days as $day) {
            if ($day['id']) {
                if ($realm === env('R1')) {
                    Day::where('id', $day['id'])
                        ->update([
                            'month_id' => $day['month_id'],
                            'date' => $day['date'],
                            'day_type' => $day['day_type']
                        ]);
                } else {
                    DayRealm::where('id', $day['id'])
                        ->update([
                            'month_id' => $day['month_id'],
                            'date' => $day['date'],
                            'day_type' => $day['day_type']
                        ]);
                }
            } else {
                if ($realm === env('R1')) {
                    Day::create($day)->save();
                } else {
                    DayRealm::create($day)->save();
                }
            }
        }
    }
}
