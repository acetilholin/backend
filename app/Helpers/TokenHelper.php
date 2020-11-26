<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class TokenHelper
{
    public function InsertToken($email, $token)
    {
        $timeNow = date('Y-m-d H:i');
        User::where('email', $email)
            ->update([
                'reset_token' => $token,
                'token_time' => $timeNow
            ]);
    }

    public function checkToken($email, $token)
    {
        $tokenTime = User::where('email', $email)->value('token_time');
        if ($this->tokenExpiration($tokenTime)) {
            return 'expired';
        } else {
            $DBToken = User::where('email', $email)->value('reset_token');
            return $token !== $DBToken ? null : 'ok';
        }
    }

    public function insertNewPassword($email, $password)
    {
        $password = Hash::make($password);
        User::where('email', $email)
            ->update(['password' => $password]);
    }

    protected function tokenExpiration($tokenTime)
    {
        $timeNow = date('Y-m-d H:i');

        $datetime1 = strtotime($tokenTime);
        $datetime2 = strtotime($timeNow);
        $minutes = ($datetime2 - $datetime1) / 60;

        return $minutes < 60 ? false : true;
    }
}
