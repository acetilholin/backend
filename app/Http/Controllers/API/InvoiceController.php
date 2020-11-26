<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\FinalInvoice;
use App\Helpers\FinalInvoiceHelper;
use App\Helpers\InvoiceHelper;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoicesResource;
use App\Invoice;
use App\Klavzula;
use App\Recipient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        return InvoicesResource::collection(Invoice::all());
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
        $invoice = request(['invoice']);
        $items = request(['items']);
        $recipient = request(['recipient']);

        $invoiceData = $invoice['invoice'];
        $items = $items['items'];
        $recipientData = $recipient['recipient'];

        $helper = new InvoiceHelper();
        $sifra_predracuna = $helper->sifraPredracuna();
        $invoiceData['sifra_predracuna'] = $sifra_predracuna;
        $invoiceData['iid'] = Str::random(5);
        $invoiceData['timestamp'] = date("Y-m-d");

        $helper->insertAllData($invoiceData, $recipientData, $items);

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
        $invoice = Invoice::where('id', $id)->first();
        $items = $invoice->items;
        $invoiceData = $invoice->getAttributes();

        $helper = new InvoiceHelper();
        $helper->copyInvoice($invoiceData, $items);

        return response()->json([
            'success' => trans('invoice.invoiceCopied'),
        ]);
    }

    public function export(Request $request)
    {
        $id = $request->id;
        $invoice = Invoice::where('id', $id)->first();
        $invoiceData = $invoice->getAttributes();

        $helper = new FinalInvoiceHelper();
        try {
            $helper->exportToFinalInvoices($invoiceData);
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
    public function show(Invoice $invoice)
    {
        $items = $invoice->items()->get();
        $invoice = collect($invoice);
        $id = $invoice->get('id');
        $customerId = $invoice->get('customer_id');
        $klavzula = $invoice->get('klavzula');

        $invoice = $invoice->all();

        $customerData = Customer::where('id', $customerId)->first();
        $customer = $customerData->getAttributes();

        $klavzulaData = Klavzula::where('short_name', $klavzula)->first();
        $klavzula = $klavzulaData->getAttributes();

        $recipientData = Recipient::where('invoice_id', $id)->first();

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
     * Display the specified resource for interval.
     *
     * @param  InvoicesResource  $invoiceitems
     * @return \Illuminate\Http\JsonResponse
     */
    public function interval(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $invoices = DB::table('invoices')
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp', 'asc')
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
    public function edit(Invoice $invoice)
    {
        $allItems = [];
        $items = $invoice->items;
        $recipient = $invoice->recipient;

        $recipient = $recipient !== null ? $recipient->getAttributes() : null;

        foreach ($items as $item) {
            $allItems[] = $item;
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
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Invoice $invoice)
    {
        $invoiceData = request(['invoice']);
        $itemsData = request(['items']);

        $id = $invoiceData['invoice']['id'];

        $finalInvoice = FinalInvoice::where('id', $id)->first();

        if ($finalInvoice) {
            unset($invoiceData['invoice']['sifra_predracuna']);
            $finalInvoice->update($invoiceData['invoice']);
        }

        $invoice->update($invoiceData['invoice']);

        $items = $itemsData['items'];
        $helper = new InvoiceHelper();
        $helper->insertData($items);
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
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return response()->json([
            'success' => trans('invoice.invoiceRemoved'),
        ], 200);
    }
}
