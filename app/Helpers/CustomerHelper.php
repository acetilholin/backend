<?php


namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerHelper
{
    public function customerValidator($request)
    {
        $table = $request->realm === env('R1') ? 'customers' : 'customers_2';
        $rules = array(
            'naziv_partnerja' => 'required|max:120',
            'posta' => 'required',
            'kraj_ulica' => [
                'required',
                Rule::unique($table)->where(function ($query) use ($request) {
                    $query->where('naziv_partnerja', $request->naziv_partnerja)
                        ->where('posta', $request->posta)
                        ->where('kraj_ulica', $request->kraj_ulica);
                })
            ]
        );

        $customerData = request(['naziv_partnerja', 'posta', 'kraj_ulica']);

        $validator = Validator::make($customerData, $rules, $this->messages());

        if ($validator->fails()) {
            $formatter = new MsgFormatterHelper();
            return $formatter->format($validator->errors()->all());
        } else {
            return null;
        }
    }

    public function customerExistsInRealm($customer)
    {
        return DB::table('customers_2')
            ->where('naziv_partnerja', 'like', '%'.$customer['naziv_partnerja'].'%')
            ->where('kraj_ulica', 'like', '%'.$customer['kraj_ulica'].'%')
            ->where('posta', 'like', '%'.$customer['posta'].'%')
            ->get();
    }

    protected function messages()
    {
        return [
            'naziv_partnerja.required' => trans('customer.nazivRequired'),
            'naziv_partnerja.max' => trans('customer.nazivPartnerjaMax'),
            'posta.required' => trans('customer.postRequired'),
            'kraj_ulica.required' => trans('customer.streetRequired'),
            'kraj_ulica.unique' => trans('customer.companyExists')
        ];
    }
}
