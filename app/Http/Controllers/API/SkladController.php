<?php

namespace App\Http\Controllers\API;

use App\Helpers\SkladHelper;
use App\Sklad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SkladResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return SkladResource::collection(Sklad::all());
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
        $data = request(['invoice_id', 'customer_id', 'created', 'item', 'work_date']);
        Sklad::create($data);
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
    public function edit(Sklad $sklad)
    {
        $helper = new SkladHelper();
        return response()->json([
            'id' => $sklad->id,
            'status' => $sklad->status,
            'item' => $sklad->item,
            'created' => $sklad->created,
            'work_date' => $sklad->work_date,
            'customer' => $helper->customer($sklad->customer_id),
            'invoice' => $helper->invoice($sklad->invoice_id)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function status($id, $status)
    {
        $status = $status > 2 ? 0 : $status;
        $sklad = Sklad::find($id);
        $sklad->status = $status;
        $sklad->save();
        return response()->json([
            'success' => trans('sklad.statusUpdated'),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sklad  $sklad
     * @return JsonResponse
     */
    public function update(Request $request, Sklad $sklad)
    {
        $skladToUpdate = request(['id', 'customer_id', 'invoice_id', 'item', 'status', 'created', 'work_date']);

        Sklad::where('id', $skladToUpdate['id'])
            ->update([
                'customer_id' => $skladToUpdate['customer_id'],
                'invoice_id' => $skladToUpdate['invoice_id'],
                'item' => $skladToUpdate['item'],
                'status' => $skladToUpdate['status'],
                'work_date' => $skladToUpdate['work_date'],
                'created' => $skladToUpdate['created']
            ]);
        return response()->json([
            'success' => trans('sklad.updated'),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sklad  $sklad
     * @return JsonResponse
     */
    public function destroy(Sklad $sklad)
    {
        $sklad->delete();
        return response()->json([
            'success' => trans('sklad.removed'),
        ], 200);
    }
}
