<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\SkladHelper;

class SkladResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $helper = new SkladHelper();
        return [
            'id' => $this->id,
            'customer' => $helper->customer($this->customer_id),
            'date' => $this->date,
            'description' => $this->description,
            'status' => $this->status,
            'final_invoice' => $helper->invoice($this->invoice_id)
        ];
    }
}
