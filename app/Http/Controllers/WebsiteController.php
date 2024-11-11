<?php

namespace App\Http\Controllers;

use App\Notifications\WebsiteContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class WebsiteController extends Controller
{
    public function sendMessage(Request $request)
    {
        $rules = array(
            'fullname' => 'required',
            'email' => 'required',
            'message' => 'required'
        );

        $data = request(['fullname', 'email', 'message']);
        $validator = Validator::make($data, $rules);

        if (!$validator->fails()) {
            Notification::route('mail', env('SEND_TO_CONTACT'))
                ->notify( new WebsiteContact($data['fullname'], $data['email'], $data['message']));
        }
    }
}
