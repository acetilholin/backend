<?php

namespace App\Http\Controllers\API;

use App\Helpers\SkladHelper;
use App\Sklad;
use App\SkladRealm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SkladResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SkladController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index($realm)
    {
        $helper = new SkladHelper();
        $sklad = $realm === env('R1') ?
            Sklad::all()->sortByDesc('id') :
            SkladRealm::all()->sortByDesc('id');

        return $helper->getAll($sklad, $realm);
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
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $data = request(['final_invoice_id', 'customer_id', 'created', 'item', 'work_date']);

        if ($request->realm === env('R1')) {
            Sklad::create($data);
        } else {
            SkladRealm::create($data);
        }

        return response()->json([
            'success' => trans('sklad.created'),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sklad  $sklad
     * @return JsonResponse
     */
    public function edit($realm, $id)
    {
        $sklad = $realm === env('R1') ? Sklad::find($id) : SkladRealm::find($id);

        $helper = new SkladHelper();
        return response()->json([
            'id' => $sklad['id'],
            'item' => $sklad['item'],
            'created' => $sklad['created'],
            'work_date' => $sklad['work_date'],
            'customer' => $helper->customer($sklad['customer_id'], $realm),
            'invoice' => $helper->finalInvoice($sklad['final_invoice_id'], $realm)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sklad  $sklad
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameters['sklad'];
        $sklad = request(['id', 'customer_id', 'final_invoice_id', 'item', 'created', 'work_date']);

        $helper = new SkladHelper();
        $table = $request->realm === env('R1') ? 'sklads' : 'sklads_2';
        $helper->updateSklad($sklad, $id, $table);

        return response()->json([
            'success' => trans('sklad.updated'),
        ], 200);
    }


    /**
     * Fitler sklads for printing
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function filter(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $realm = $request->realm;
        $helper = new SkladHelper();

        $sklads = $helper->filterSklads($from, $to, $realm);

        return response()->json([
            'sklads' => $sklads,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sklad  $sklad
     * @return JsonResponse
     */
    public function destroy($realm, $id)
    {
        $sklad = $realm === env('R1') ? Sklad::find($id) : SkladRealm::find($id);
        $sklad->delete();
        return response()->json([
            'success' => trans('sklad.removed'),
        ], 200);
    }
}
