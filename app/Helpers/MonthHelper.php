<?php


namespace App\Helpers;

use App\Day;
use App\Employee;
use App\Month;
use Illuminate\Support\Facades\DB;

class MonthHelper
{
    public function insert($daysData, $reportData)
    {
        $monthNo = $reportData['month_no'];
        $date = date("Y").'-'.$monthNo.'-01';
        $reportData['date'] = $date;
        $month = Month::create($reportData);
        $month = $month->getAttributes();
        $month_id = $month['id'];

        if ($daysData) {
            foreach ($daysData as $days) {
                unset($days['id']);
                $days['month_id'] = $month_id;
                Day::create($days);
            }
        }
    }

    public function filter($from, $to, $employee_id)
    {
        if ($employee_id === 0) {
            $months = DB::table('months')
                ->whereBetween('date', [$from, $to])
                ->orderBy('date', 'asc')
                ->get();
        } else {
            $months = DB::table('months')
                ->whereBetween('date', [$from, $to])
                ->where('employee_id', $employee_id)
                ->orderBy('date', 'asc')
                ->get();
        }

        return $months;
    }

    public function employee($id)
    {
        $collection = Employee::where('id', $id)->first();
        $employee = $collection->getAttributes();
        return $employee['person'];
    }

    public function getDays($mid)
    {
        $allDays = [];
        $days = Day::where('month_id', $mid)->get();
        foreach ($days as $day) {
            $allDays[] = $day->getAttributes();
        }
        return $allDays;
    }

    public function copy($month, $days)
    {
        unset($month['id']);
        $month = Month::create($month);
        $month = $month->getAttributes();
        $id = $month['id'];

        if ($days) {
            foreach ($days as $day) {
                $day = $day->getAttributes();
                unset($day['id']);
                $day['month_id'] = $id;
                Day::create($day);
            }
        }
    }

    public function update($days)
    {
        foreach ($days as $day) {
            if ($day['id']) {
                Day::where('id', $day['id'])
                    ->update([
                        'month_id' => $day['month_id'],
                        'date' => $day['date'],
                        'day_type' => $day['day_type']
                    ]);
            } else {
                Day::create($day)->save();
            }
        }
    }
}
