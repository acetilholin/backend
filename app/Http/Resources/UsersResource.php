<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\UserHelper;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $helper = new UserHelper();
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'enabled' => $this->enabled,
            'role' => $this->role,
            'picture' => $this->picture,
            'online' => $helper->online($this->id),
            'last_seen' => $this->last_seen,
            'country' => $this->country
        ];
    }
}
