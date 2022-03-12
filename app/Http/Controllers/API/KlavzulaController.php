<?php

namespace App\Http\Controllers\API;

use App\Klavzula;
use App\KlavzulaRealm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KlavzulaController extends Controller
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
    public function index($realm)
    {
        $klavzule = $realm === env('R1') ? Klavzula::all() : KlavzulaRealm::all();
        return response()->json([
            'klavzule' => $klavzule
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $klavzulaData = request(['short_name', 'description']);

        if ($request->realm === env('R1')) {
            Klavzula::create($klavzulaData)->save();
        } else {
            KlavzulaRealm::create($klavzulaData)->save();
        }


        return response()->json([
            'success' => trans('klavzule.klavzulaCreated')
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Klavzula  $klavzula
     * @return \Illuminate\Http\Response
     */
    public function show(Klavzula $klavzula)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Klavzula  $klavzula
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($realm, $id)
    {
        $klavzula = $realm === env('R1') ?
            Klavzula::find($id) :
            KlavzulaRealm::find($id);

        return response()->json([
            'klavzula' => $klavzula
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Klavzula  $klavzula
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameters['klavzula'];
        $klavzula = $request->realm === env('R1') ?
            Klavzula::where('id', $id)->first() :
            KlavzulaRealm::where('id', $id)->first();

        $klavzulaData = request(['short_name', 'description']);
        $klavzula->update($klavzulaData);
        return response()->json([
            'success' => trans('klavzule.klavzulaUpdated')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Klavzula  $klavzula
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($realm, $id)
    {
        $klavzula = $realm === env('R1') ?
            Klavzula::where('id', $id)->first() :
            KlavzulaRealm::where('id', $id)->first();

        $klavzula->delete();
        return response()->json([
            'success' => trans('klavzule.klavzulaDeleted')
        ], 200);
    }
}
