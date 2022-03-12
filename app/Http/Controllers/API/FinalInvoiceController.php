<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\CustomerRealm;
use App\FinalInvoice;
use App\FinalInvoiceRealm;
use App\Helpers\FinalInvoiceHelper;
use App\Invoice;
use App\Item;
use App\ItemRealm;
use App\Klavzula;
use App\KlavzulaRealm;
use App\Recipient;
use App\RecipientRealm;
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
    public function index(Request $request)
    {
        $table = $request->route()->parameters['realm'] === env('R1') ?
            'final_invoices' : 'final_invoices_2';

        $helper = new FinalInvoiceHelper();
        $finalAll = $helper->getAllAndSort($table);
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
    public function perYear($realm, $year)
    {
        $helper = new FinalInvoiceHelper();
        $table = $realm === env('R1') ?
            'final_invoices' : 'final_invoices_2';
        $finalInvoices = $helper->finalPerYear($table, $year);
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
    public function show($realm, $id)
    {
        if ($realm === env('R1')) {
            $attr = FinalInvoice::where('id', $id)->first();
        } else {
            $attr = FinalInvoiceRealm::where('id', $id)->first();
        }

        $finalInvoice = $attr->getAttributes();

        $customerData = $realm === env('R1') ?
            Customer::where('id', $finalInvoice['customer_id'])->first() :
            CustomerRealm::where('id', $finalInvoice['customer_id'])->first();
        $customer = $customerData->getAttributes();

        $items = $realm === env('R1') ? Item::where('invoice_id', $id)->get() :
            ItemRealm::where('invoice_id', $id)->get();

        $recipientData = $realm === env('R1') ? Recipient::where('invoice_id', $id)->first() :
            RecipientRealm::where('invoice_id', $id)->first();

        $recipient = $recipientData !== null ? $recipientData->getAttributes() : null;

        $allItems = [];

        foreach ($items as $item) {
            $allItems[] = $item->getAttributes();
        }

        $klavzulaData = $realm === env('R1') ?
            Klavzula::where('short_name', $finalInvoice['klavzula'])->first() :
            KlavzulaRealm::where('short_name', $finalInvoice['klavzula'])->first();

        $klavzula = isset($klavzulaData) ? $klavzulaData->getAttributes() : null;

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
     * Get final invoice for specific customer.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function fromCustomer($realm, $id)
    {
        $table = $realm === env('R1') ?
            'final_invoices' : 'final_invoices_2';
        $final = DB::table($table)->where('customer_id', $id)->get();
        return response()->json([
            'final' => $final
        ]);
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
        $realm = $request->realm;

        $table = $request->realm === env('R1') ?
            'final_invoices' : 'final_invoices_2';

        $allInvoices = [];

        $helper = new FinalInvoiceHelper();

        $finalInvoices = $helper->getIntervalAndSort($table, $from, $to);

        foreach ($finalInvoices as $invoice) {
            $noVAT = 0;
            $items = Item::where('invoice_id', $invoice->id)->get();

            foreach ($items as $item) {
                $item = $item->getAttributes();
                $noVAT += $item['total_price'];
            }

            $customerId = $invoice->customer_id;

            $customerData = $realm === env('R1') ?
                Customer::where('id', $customerId)->first() :
                CustomerRealm::where('id', $customerId)->first();
            $customer = $customerData->getAttributes();

            $invoice->kraj_ulica = $customer['kraj_ulica'];
            $invoice->id_ddv = $customer['id_ddv'];
            $invoice->posta = $customer['posta'];
            $tujina = (bool) $customer['tujina'];

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

        $table = $request->realm === env('R1') ?
            'final_invoices' : 'final_invoices_2';

        $allInvoices = [];

        $finalInvoices = DB::table($table)
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
    public function destroy($realm, $id)
    {
        $finalInvoice = $realm === env('R1') ?
            FinalInvoice::find($id) : FinalInvoiceRealm::find($id);

        $finalInvoice->delete();
        return response()->json([
            'success' => trans('final.finalInvoiceDeleted')
        ], 200);
    }
}
