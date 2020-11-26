<?php

namespace App\Http\Controllers\API;

use App\Klavzula;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KlavzulaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $klavzule = Klavzula::all();
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

        Klavzula::create($klavzulaData)->save();
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
    public function edit(Klavzula $klavzula)
    {
        return response()->json([
            'klavzula' => $klavzula->getAttributes()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Klavzula  $klavzula
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Klavzula $klavzula)
    {
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
    public function destroy(Klavzula $klavzula)
    {
        $klavzula->delete();
        return response()->json([
            'success' => trans('klavzule.klavzulaDeleted')
        ], 200);
    }
}
