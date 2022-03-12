<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $invoice = $this->resource;
        return [
            'id' => $invoice['id'],
            'sifra_predracuna' => $invoice['sifra_predracuna'],
            'ime_priimek' => $invoice['ime_priimek'],
            'total' => $invoice['total'],
            'vat' => $invoice['vat'],
            'timestamp' => $invoice['timestamp'],
            'expiration' => $invoice['expiration'],
            'quantity' => $invoice['quantity'],
            'work_date' => $invoice['work_date'],
            'klavzula' => $invoice['klavzula']
        ];
    }
}
