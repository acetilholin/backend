<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\FinalInvoice;
use App\Helpers\FinalInvoiceHelper;
use App\Item;
use App\Klavzula;
use App\Recipient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FinalInvoiceController extends Controller
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
        $helper = new FinalInvoiceHelper();
        $finalAll = $helper->getAllAndSort();
        return response()->json([
            'final' => $finalAll
        ]);
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
     * Display final invoices for specific year.
     *
     * @param  \App\FinalInvoice  $finalInvoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function perYear($year)
    {
        $helper = new FinalInvoiceHelper();
        $finalInvoices = $helper->finalPerYear($year);
        $allInvoices = [];

        foreach ($finalInvoices as $invoice) {
            $allInvoices[] = $invoice;
        }

        return response()->json([
            'finalInvoices' => $allInvoices,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FinalInvoice  $finalInvoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(FinalInvoice $finalInvoice)
    {
        $finalInvoice = collect($finalInvoice);
        $id = $finalInvoice->get('id');
        $customerId = $finalInvoice->get('customer_id');
        $klavzulaShort = $finalInvoice->get('klavzula');

        $customerData = Customer::where('id', $customerId)->first();
        $customer = $customerData->getAttributes();
        $items = Item::where('invoice_id', $id)->get();

        $recipientData = Recipient::where('invoice_id', $id)->first();
        $recipient = $recipientData !== null ? $recipientData->getAttributes() : null;

        $allItems = [];

        foreach ($items as $item) {
            $allItems[] = $item->getAttributes();
        }

        $klavzulaData = Klavzula::where('short_name', $klavzulaShort)->first();
        $klavzula = $klavzulaData->getAttributes();

        $finalInvoice = $finalInvoice->all();

        return response()->json([
            'items' => $allItems,
            'invoice' => $finalInvoice,
            'customer' => $customer,
            'recipient' => $recipient,
            'klavzula' => $klavzula
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FinalInvoice  $finalInvoice
     * @return \Illuminate\Http\Response
     */
    public function edit(FinalInvoice $finalInvoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FinalInvoice  $finalInvoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, FinalInvoice $finalInvoice)
    {

    }

    /**
     * Get final invoices for report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function report(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $allInvoices = [];

        $helper = new FinalInvoiceHelper();

        $finalInvoices = $helper->getIntervalAndSort($from, $to);

        foreach ($finalInvoices as $invoice) {
            $noVAT = 0;
            $items = Item::where('invoice_id', $invoice->id)->get();

            foreach ($items as $item) {
                $item = $item->getAttributes();
                $noVAT += $item['total_price'];
            }

            $customerId = $invoice->customer_id;
            $customerData = Customer::where('id', $customerId)->first();
            $customer = $customerData->getAttributes();

            $invoice->kraj_ulica = $customer['kraj_ulica'];
            $invoice->id_ddv = $customer['id_ddv'];
            $invoice->posta = $customer['posta'];
            $tujina = $customer['tujina'] ? true : false;

            $invoice->tujina = $tujina;
            $invoice->noVAT = $noVAT;
            $allInvoices[] = $invoice;
        }

        return response()->json([
            'final' => $allInvoices,
            'from' => $from,
            'to' => $to
        ]);
    }

    /**
     * Get final invoices for specific interval
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function interval(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $allInvoices = [];

        $finalInvoices = DB::table('final_invoices')
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp', 'asc')
            ->get();

        foreach ($finalInvoices as $invoice) {
            $allInvoices[] = $invoice;
        }

        return response()->json([
            'finalInvoices' => $allInvoices,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FinalInvoice  $finalInvoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(FinalInvoice $finalInvoice)
    {
        $finalInvoice->delete();
        return response()->json([
            'success' => trans('final.finalInvoiceDeleted')
        ], 200);
    }
}
