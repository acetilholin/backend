<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\CustomerRealm;
use App\FinalInvoice;
use App\FinalInvoiceRealm;
use App\Helpers\FinalInvoiceHelper;
use App\Helpers\InvoiceHelper;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoicesResource;
use App\Invoice;
use App\InvoiceRealm;
use App\Klavzula;
use App\KlavzulaRealm;
use App\Recipient;
use App\RecipientRealm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function index(Request $request)
    {
        if ($request->route()->parameters['realm'] === env('R1')) {
            return InvoicesResource::collection(Invoice::where('deleted', '0')->orderByDesc('id')->get());
        } else {
            return InvoicesResource::collection(InvoiceRealm::where('deleted', '0')->orderByDesc('id')->get());
        }
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
        $realm = $request->route()->parameters['realm'];
        $invoice = request(['invoice']);
        $items = request(['items']);
        $recipient = request(['recipient']);

        $invoiceData = $invoice['invoice'];
        $items = $items['items'];
        $recipientData = $recipient['recipient'];
        $helper = new InvoiceHelper();

        if (!$invoiceData['sifra_predracuna']) {
            $sifra_predracuna = $helper->sifraPredracuna($realm);
            $invoiceData['sifra_predracuna'] = $sifra_predracuna;
        }

        $invoiceData['iid'] = Str::random(5);
        $invoiceData['timestamp'] = date("Y-m-d");

        $helper->insertAllData($invoiceData, $recipientData, $items, $realm);

        return response()->json([
            'success' => trans('invoice.invoiceSaved')
        ], 200);
    }

    /**
     * Copying invoice
     *
     * @param  \Illuminate\Http\Request  $request
     * return view
     */
    public function copy(Request $request)
    {
        $id = $request->id;
        $realm = $request->realm;

        $invoice = $realm === env('R1') ? Invoice::where('id', $id)->first() : InvoiceRealm::where('id', $id)->first();

        $items = $invoice->items;
        $invoiceData = $invoice->getAttributes();

        $helper = new InvoiceHelper();
        $helper->copyInvoice($invoiceData, $items, $realm);

        return response()->json([
            'success' => trans('invoice.invoiceCopied'),
        ]);
    }

    public function export(Request $request)
    {
        $id = $request->id;
        $realm = $request->realm;

        $invoice = $realm === env('R1') ? Invoice::where('id', $id)->first() : InvoiceRealm::where('id', $id)->first();
        $invoiceData = $invoice->getAttributes();

        $helper = new FinalInvoiceHelper();
        try {
            $helper->exportToFinalInvoices($invoiceData, $realm);
            return response()->json([
                'success' => trans('invoice.invoiceExportedToFinal'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($realm, $id)
    {
        $invoice = $realm === env('R1') ? Invoice::where('id', $id)->first() : InvoiceRealm::where('id', $id)->first();
        $invoice = $invoice->getAttributes();
        $items = $realm === env('R1') ? Invoice::find($id)->items : InvoiceRealm::find($id)->items;
        $customerData = $realm === env('R1') ?
            Customer::where('id', $invoice['customer_id'])->first() :
            CustomerRealm::where('id', $invoice['customer_id'])->first();

        $recipientData = $realm === env('R1') ?
            Recipient::where('invoice_id', $id)->first() :
            RecipientRealm::where('invoice_id', $id)->first();

        $klavzulaData = $realm === env('R1') ?
            Klavzula::where('short_name', $invoice['klavzula'])->first() :
            KlavzulaRealm::where('short_name', $invoice['klavzula'])->first();

        $customer = $customerData->getAttributes();
        $klavzula = isset($klavzulaData) ? $klavzulaData->getAttributes() : null;
        $recipient = $recipientData !== null ? $recipientData->getAttributes() : null;

        $allItems = [];

        foreach ($items as $item) {
            $allItems[] = $item->getAttributes();
        }

        return response()->json([
            'items' => $allItems,
            'invoice' => $invoice,
            'customer' => $customer,
            'recipient' => $recipient,
            'klavzula' => $klavzula
        ]);
    }

    /**
     * Display invoice for specific year.
     *
     * @param  InvoicesResource  $invoiceitems
     * @return \Illuminate\Http\JsonResponse
     */
    public function perYear($realm, $year)
    {
        $table = $realm === env('R1') ? 'invoices' : 'invoices_2';
        $helper = new InvoiceHelper();
        $invoices = $helper->invoicePerYear($year, $table);
        $allInvoices = [];

        foreach ($invoices as $invoice) {
            $allInvoices[] = $invoice;
        }

        return response()->json([
            'invoices' => $allInvoices,
        ]);
    }

    /**
     * Display the specified resource for interval.
     *
     * @param  InvoicesResource  $invoiceitems
     * @return \Illuminate\Http\JsonResponse
     */
    public function interval(Request $request)
    {
        $realm = $request->realm;
        $from = $request->from;
        $to = $request->to;
        $allInvoices = [];

        $table = $realm === env('R1') ? 'invoices' : 'invoices_2';

        $invoices = DB::table($table)
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('id', 'ASC')
            ->get();

        foreach ($invoices as $invoice) {
            $allInvoices[] = $invoice;
        }

        return response()->json([
            'invoices' => $allInvoices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($realm, $id)
    {
        $allItems = [];
        $invoice = $realm === env('R1') ?
            Invoice::where('id', $id)->first() : InvoiceRealm::where('id', $id)->first();
        $items = $realm === env('R1') ?
            Invoice::find($id)->items : InvoiceRealm::find($id)->items;

        $recipient = $realm === env('R1') ?
            Invoice::find($id)->recipient : InvoiceRealm::find($id)->recipient;

        $invoice = $invoice->getAttributes();

        $recipient = $recipient ? $recipient->getAttributes() : null;

        foreach ($items as $item) {
            $allItems[] = $item->getAttributes();
        }

        return response()->json([
            'invoice' => InvoiceResource::make($invoice),
            'items' => $allItems,
            'recipient' => $recipient
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($realm, $id)
    {
        $invoiceData = request(['invoice']);
        $itemsData = request(['items']);
        $table = $realm === env('R1') ? 'items' : 'items_2';

        $finalInvoice = $realm === env('R1') ? FinalInvoice::where('id', $id)->first() :
            FinalInvoiceRealm::where('id', $id)->first();

        $invoice = $realm === env('R1') ?
            Invoice::where('id', $id)->first() : InvoiceRealm::where('id', $id)->first();

        $invoice->update($invoiceData['invoice']);

        if ($finalInvoice) {
            unset($invoiceData['invoice']['sifra_predracuna']);
            $finalInvoice->update($invoiceData['invoice']);
        }

        $items = $itemsData['items'];
        $helper = new InvoiceHelper();
        $helper->insertData($table, $items);
        $allItems = [];

        $items = $invoice->items;

        foreach ($items as $item) {
            $allItems[] = $item;
        }

        return response()->json([
            'success' => trans('invoice.invoiceUpdated'),
            'items' => $allItems
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($realm, $id)
    {
        $invoice = $realm === env('R1') ? Invoice::find($id) : InvoiceRealm::find($id);
        $invoice->update(['deleted' => 1]);

        $finalInvoice = $realm === env('R1') ? FinalInvoice::where('iid', $invoice->iid)->first() :
            FinalInvoiceRealm::where('iid', $invoice->iid)->first();

        if ($finalInvoice) {
            $finalInvoice->delete();
        }

        return response()->json([
            'success' => trans('invoice.invoiceRemoved'),
        ], 200);
    }


    /**
     * Check if sifra already exists.
     *
     * @param  \App\Invoice  $sifra
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIfSifraExists(Request $request)
    {
        $realm = $request->realm;
        $invoice = $realm === env('R1') ?
            Invoice::where('sifra_predracuna', $request->sifra)->first() :
            InvoiceRealm::where('sifra_predracuna', $request->sifra)->first();
        return response()->json([
            'data' => $invoice !== null,
        ], 200);
    }
}
