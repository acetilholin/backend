<?php

namespace App\Http\Controllers\API;

use App\Day;
use App\Employee;
use App\Helpers\MonthHelper;
use App\Http\Resources\MonthResource;
use App\Month;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MonthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return MonthResource::collection(Month::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $days = request(['days']);
        $report = request(['report']);

        $daysData = $days['days'];
        $reportData = $report['report'];

        $helper = new MonthHelper();
        $helper->insert($daysData, $reportData);

        return response()->json([
            'success' => trans('month.monthSaved')
        ], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Month  $month
     * @return \Illuminate\Http\Response
     */
    public function show(Month $month)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Month  $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Month $month)
    {
        $month = $month->getAttributes();
        $eId = $month['employee_id'];
        $mid = $month['id'];

        $helper = new MonthHelper();
        $employeeData = Employee::where('id', $eId)->first();
        $employee = $employeeData->getAttributes();
        $person = $employee['person'];
        $address = $employee['address'];
        $posta = $employee['posta'];

        $days = $helper->getDays($mid);

        return response()->json([
            'month' => $month,
            'employee' => $person,
            'address' => $address,
            'posta' => $posta,
            'eId' => $eId,
            'days' => $days
        ], 200);
    }

    /**
     * Filter data by interval
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function interval(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $employee_id = $request->employee_id;

        if (!$from && !$to) {
            $months = Month::where('employee_id', $employee_id)->get();
        } else {
            $helper = new MonthHelper();
            $months = $helper->filter($from, $to, $employee_id);
        }

        return MonthResource::collection($months);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Month  $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Month $month)
    {
        $data = request(['days', 'month', 'employee_id']);
        $monthData = $data['month'];
        $daysData = $data['days'];
        $monthData['employee_id'] = $data['employee_id'];
        $allDays = [];

        try {

            $month->update($monthData);
            $helper = new MonthHelper();
            $helper->update($daysData);

            $days = $month->days;

            foreach ($days as $day) {
                $allDays[] = $day;
            }

            return response()->json([
                'success' => trans('month.monthUpdated'),
                'days' => $allDays
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => trans('month.cannotUpdate')], 401);
        }
    }

    /**
     * Copy month report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function copy(Request $request)
    {
        $id = $request->id;

        $monthData = Month::where('id', $id)->first();
        $month = $monthData->getAttributes();

        $days = Day::where('month_id', $id)->get();

        $helper = new MonthHelper();
        $helper->copy($month, $days);

        return response()->json([
            'success' => trans('month.monthCopied'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Month  $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Month $month)
    {
        $month->delete();
        return response()->json([
            'success' => trans('month.monthRemoved'),
        ], 200);
    }
}
