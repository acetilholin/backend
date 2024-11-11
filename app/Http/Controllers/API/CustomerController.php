<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\CustomerRealm;
use App\FinalInvoice;
use App\FinalInvoiceRealm;
use App\Helpers\CustomerHelper;
use App\Http\Resources\CustomersResource;
use App\Invoice;
use App\InvoiceRealm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $realm = $request->realm;
        $customers = $realm === env('R1') ?
            Customer::where('deleted', 0)->orderBy('id', 'desc')->get() :
            CustomerRealm::where('deleted', 0)->orderBy('id', 'desc')->get();

        return CustomersResource::collection($customers);
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
        $helper = new CustomerHelper();
        $validation = $helper->customerValidator($request);

        if ($validation) {
            return response()->json(['error' => $validation], 401);
        }

        $customerData = request(['naziv_partnerja', 'kraj_ulica', 'posta', 'email', 'telefon', 'id_ddv', 'sklic_st']);

        if ($request->realm === env('R1')) {
            Customer::create($customerData)->save();
        } else {
            CustomerRealm::create($customerData)->save();
        }

        return response()->json([
            'success' => trans('customer.customerCreated')
        ], 200);
    }

    /**
     * Export customer to another realm.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportToRealm($realm, $id)
    {
        $customer = Customer::find($id);

        $helper = new CustomerHelper();
        $customerRealm = $helper->customerExistsInRealm($customer);

        if ($customerRealm->isEmpty()) {
            $dataToInsert = $customer->getAttributes();
            unset($dataToInsert['id']);
            CustomerRealm::create($dataToInsert);
            return response()->json([
                'success' => trans('customer.exportedToRealm')
            ], 200);
        } else {
            return response()->json(['error' => trans('customer.alreadyExists')], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($realm, $id)
    {
        $customer = $realm === env('R1') ?
            Customer::where('id', $id)->first() :
            CustomerRealm::where('id', $id)->first();

        $allInvoices = [];
        $allFinalInvoices = [];

        $invoices = $customer->invoices()
            ->where('customer_id', $id)
            ->where('deleted', 0)
            ->get();
        $finalInvoices = $customer->finalInvoices()
            ->where('customer_id', $id)
            ->where('deleted', 0)
            ->get();

        foreach ($invoices as $invoice) {
            $allInvoices[] = $invoice->getAttributes();
        }

        foreach ($finalInvoices as $invoice) {
            $allFinalInvoices[] = $invoice->getAttributes();
        }

        return response()->json([
            'invoices' => $allInvoices,
            'final' => $allFinalInvoices
        ]);
    }

    /**
     * Display invoices invoices for specific interval
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fromToFinal(Request $request)
    {
        if ($request->realm === env('R1')) {
            $finalInvoices = FinalInvoice::whereBetween('timestamp', [$request->from, $request->to])
                ->where('customer_id', $request->customer_id)
                ->get();
        } else {
            $finalInvoices = FinalInvoiceRealm::whereBetween('timestamp', [$request->from, $request->to])
                ->where('customer_id', $request->customer_id)
                ->get();
        }

        return response()->json([
            'final' => $finalInvoices
        ]);
    }

    /**
     * Display invoices invoices for specific interval
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fromToInvoice(Request $request)
    {
        if ($request->realm === env('R1')) {
            $invoices = Invoice::whereBetween('timestamp', [$request->from, $request->to])
                ->where('customer_id', $request->customer_id)
                ->get();
        } else {
            $invoices = InvoiceRealm::whereBetween('timestamp', [$request->from, $request->to])
                ->where('customer_id', $request->customer_id)
                ->get();
        }

        return response()->json([
            'invoices' => $invoices
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($realm, $id)
    {
        $customer = $realm === env('R1') ?
            Customer::where('id', $id)->first() :
            CustomerRealm::where('id', $id)->first();

        return response()->json([
            'customer' => $customer->getAttributes()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = $request->route()->parameters['customer'];
        $customer = $request->realm === env('R1') ?
            Customer::where('id', $id)->first() :
            CustomerRealm::where('id', $id)->first();

        $customerData = request(['naziv_partnerja', 'kraj_ulica', 'posta', 'email', 'telefon', 'id_ddv', 'sklic_st']);
        $customer->update($customerData);
        return response()->json([
            'success' => trans('customer.customerEdited')
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($realm, $id)
    {
        $customer = $realm === env('R1') ? Customer::find($id) : CustomerRealm::find($id);
        $customer->update(['deleted' => 1]);

        $invoices = $customer->invoices()
            ->where('customer_id', $id)
            ->where('deleted', 0)
            ->get();

        foreach ($invoices as $invoice) {
            $invoice = $realm === env('R1') ? Invoice::find($invoice->id) : InvoiceRealm::find($invoice->id);
            $invoice->deleted = 1;
            $invoice->save();
        }

        $finalInvoices = $customer->finalInvoices()
            ->where('customer_id', $id)
            ->where('deleted', 0)
            ->get();

        foreach ($finalInvoices as $finalInvoice) {
            $finalInvoice = $realm === env('R1') ? FinalInvoice::find($finalInvoice->id) : FinalInvoiceRealm::find($finalInvoice->id);
            $finalInvoice->delete();
        }

        return response()->json([
            'success' => trans('customer.customerDeleted')
        ], 200);
    }
}
