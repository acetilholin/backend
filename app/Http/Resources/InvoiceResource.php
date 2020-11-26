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
        return [
            'id' => $this->id,
            'sifra_predracuna' => $this->sifra_predracuna,
            'ime_priimek' => $this->ime_priimek,
            'total' => $this->total,
            'vat' => $this->vat,
            'timestamp' => $this->timestamp,
            'expiration' => $this->expiration,
            'quantity' => $this->quantity,
            'work_date' => $this->work_date,
            'klavzula' => $this->klavzula
        ];
    }
}
