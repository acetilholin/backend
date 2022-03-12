<?php

namespace App\Http\Controllers\API;

use App\Day;
use App\DayRealm;
use App\Employee;
use App\Helpers\MonthHelper;
use App\Http\Resources\MonthResource;
use App\Month;
use App\MonthRealm;
use Google\Service\GameServices\Realm;
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
    public function index($realm)
    {
        $months = $realm === env('R1') ?
            Month::all()->sortByDesc('id') :
            MonthRealm::all()->sortByDesc('id');

        return MonthResource::collection($months);
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
        $realm = $request->realm;

        $daysData = $days['days'];
        $reportData = $report['report'];

        $helper = new MonthHelper();
        $helper->insert($daysData, $reportData, $realm);

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
    public function edit($realm, $id)
    {
        $month = $realm === env('R1') ? Month::find($id) : MonthRealm::find($id);

        $eId = $month['employee_id'];
        $mid = $month['id'];

        $helper = new MonthHelper();
        $employee = Employee::find($eId);
        $days = $helper->getDays($mid, $realm);

        return response()->json([
            'month' => $month,
            'employee' => $employee['person'],
            'address' => $employee['address'],
            'posta' => $employee['posta'],
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
        $realm = $request->realm;

        if (!$from && !$to) {
            $months = $realm === env('R1') ? Month::where('employee_id', $employee_id)->get() :
                MonthRealm::where('employee_id', $employee_id)->get();
        } else {
            $helper = new MonthHelper();
            $months = $helper->filter($from, $to, $employee_id, $realm);
        }

        return MonthResource::collection($months);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameters['month'];
        $data = request(['days', 'month', 'employee_id']);

        $monthData = $data['month'];
        $daysData = $data['days'];

        $monthData['employee_id'] = $data['employee_id'];
        $allDays = [];
        $month = $request->realm === env('R1') ? Month::find($id) : MonthRealm::find($id);

        try {
            $month->update($monthData);
            $helper = new MonthHelper();
            $helper->update($daysData, $request->realm);

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
    public function copy($realm, $id)
    {
        $monthData = $realm === env('R1') ? Month::find($id) : MonthRealm::find($id);
        $month = $monthData->getAttributes();

        $days = $realm === env('R1') ?
            Day::where('month_id', $id)->get() :
            DayRealm::where('month_id', $id)->get();

        $helper = new MonthHelper();
        $helper->copy($month, $days, $realm);

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
    public function destroy($realm, $id)
    {
        $month = $realm === env('R1') ? Month::find($id) : MonthRealm::find($id);
        $month->delete();
        return response()->json([
            'success' => trans('month.monthRemoved'),
        ], 200);
    }
}
