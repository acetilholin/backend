<?php

namespace App\Http\Controllers\API;

use App\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $klavzuleData = Setting::where('data', 'klavzule')
            ->where('user_id', auth()->user()->id)
            ->first();

        $companyData = Setting::where('data', 'company')
            ->where('user_id', auth()->user()->id)
            ->first();

        $realm = Setting::where('data', 'realm')
            ->where('user_id', auth()->user()->id)
            ->first();

        $klavzule = $klavzuleData->getAttributes();
        $company = $companyData->getAttributes();

        return response()->json([
            'klavzule' => $klavzule,
            'company' => $company,
            'realm' => $realm
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Setting  $settings
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $settings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Setting  $settings
     * @return \Illuminate\Http\Response
     */
    public function edit(Setting $settings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Setting  $settings
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Setting $settings)
    {
        $data = request(['visible', 'data']);
        DB::table('settings')
            ->updateOrInsert(
                ['user_id' => auth()->user()->id, 'data' => $data['data']],
                [ 'visible' => $data['visible'],
                    'data' => $data['data'],
                    'user_id' => auth()->user()->id
                ]
            );

        return response()->json([
            'success' => trans('settings.settingChanged')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Setting  $settings
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $settings)
    {
        //
    }
}
