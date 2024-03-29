<?php

namespace App\Http\Controllers\API;

use App\Recipient;
use App\RecipientRealm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RecipientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $recipientData = request(['invoice_id', 'title', 'street', 'posta']);

        if ($request->realm === env('R1')) {
            Recipient::create($recipientData)->save();
        } else {
            RecipientRealm::create($recipientData)->save();
        }

        $recipient = $request->realm === env('R1') ?
            Recipient::where('invoice_id', $recipientData['invoice_id'])->first() :
            RecipientRealm::where('invoice_id', $recipientData['invoice_id'])->first();

        $recipient = $recipient->getAttributes();

        return response()->json([
            'success' => trans('recipient.recipientAdded'),
            'recipient' => $recipient
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function show(Recipient $recipient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\Response
     */
    public function edit(Recipient $recipient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $recipient = $request->id === env('R1') ?
            Recipient::where('id', $request->id)->first() :
            RecipientRealm::where('id', $request->id)->first();

        $recipientData = request(['id', 'title', 'street', 'posta']);
        $recipient->update($recipientData);

        $recipient = $request->id === env('R1') ?
            Recipient::find($recipientData['id']) :
            RecipientRealm::find($recipientData['id']);

        $recipient = $recipient->getAttributes();

        return response()->json([
            'success' => trans('recipient.recipientUpdated'),
            'recipient' => $recipient
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Recipient  $recipient
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($realm, $id)
    {
        $recipient = $realm === env('R1') ? Recipient::find($id) : RecipientRealm::find($id);
        $recipient->delete();

        return response()->json([
            'success' => trans('recipient.recipientRemoved'),
        ], 200);
    }
}
