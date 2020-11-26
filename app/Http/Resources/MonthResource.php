<?php

namespace App\Http\Resources;

use App\Helpers\MonthHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $helper = new MonthHelper();
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'employee' => $helper->employee($this->employee_id)
        ];
    }
}
