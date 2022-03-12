<?php


namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerHelper
{
    public function customerValidator($request)
    {
        $rules = array(
            'naziv_partnerja' => 'required|max:120',
            'posta' => 'required',
            'kraj_ulica' => 'required|unique:customers'
        );

        $customerData = request(['naziv_partnerja', 'posta', 'kraj_ulica']);

        $validator = Validator::make($customerData, $rules, $this->messages());

        if ($validator->fails()) {
            $formatter = new MsgFormatterHelper();
            $messages = $formatter->format($validator->errors()->all());
            return $messages;
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
