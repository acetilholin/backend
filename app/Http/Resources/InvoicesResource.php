<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sifra_predracuna' => $this->sifra_predracuna,
            'ime_priimek' => $this->ime_priimek,
            'total' => $this->total,
            'timestamp' => $this->timestamp,
            'expiration' => $this->expiration,
            'invoice_id' => $this->invoice_id,
            'work_date' => $this->work_date,
            'customer_id' => $this->customer_id,
            'avans' => $this->avans,
            'deleted' => $this->deleted,
            'avans_after_invoice' => $this->avans_after_invoice,
            'final_discount' => $this->final_discount,
            'related_to_invoice' => $this->related_to_invoice
        ];
    }
}
