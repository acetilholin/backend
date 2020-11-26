<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomersResource extends JsonResource
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
            'naziv_partnerja' => $this->naziv_partnerja,
            'email' => $this->email,
            'kraj_ulica' => $this->kraj_ulica,
            'posta' => $this->posta,
            'telefon' => $this->telefon
        ];
    }
}
