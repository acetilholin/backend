<?php


namespace App\Helpers;

use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserHelper
{
    public function editSingleAttr($request)
    {
        $id = $request->id;
        $attr = $request->attr;

        if ($attr === 'enabled') {
            $data = $request->data == 'true' ? 1 : 0;
        } else {
            if ($request->data == 'admin') {
                $data = 'user';
            } else {
                $data = 'admin';
            }
        }

        User::where('id', $id)
            ->update([$attr => $data]);
    }

    public function getUserIpAndCountry()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $country = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
        $userCountry = $country->geoplugin_countryCode;
        return $ipAndCountry = array(
            'ip' => $ip,
            'country' => strtolower($userCountry)
        );
    }

    public function online($id)
    {
        return Cache::has('user-is-online-'.$id) ? 'online' :'offline';
    }

    public function lastSeen($user)
    {
        $id = $user->id;
        $country = $this->getUserIpAndCountry()['country'];
        $country = $country !== null ? $country : 'si';
        User::where('id', $id)
            ->update([
                'last_seen' => date("Y-m-d H:i"),
                'country' => $country
            ]);
    }

    public function checkPassword($id, $password)
    {
        $user = User::where('id', $id)->first();
        return Hash::check($password, $user->password);
    }

    public function updatePassword($id, $password)
    {
        $password = Hash::make($password);
        User::where('id', $id)
            ->update(['password' => $password]);
    }

    public function checkPermissions($operation, $user, $uid)
    {
        $currentUser = User::find($uid);

        if ($user->email == env('ADMIN')) {
            return trans('user.cannotChangeAdmin');
        } else {
            if ($currentUser->role === 'user') {
                return trans('user.notEnoughRights');
            } else {
                if ($user->id === $uid && $operation == 'delete') {
                    return trans('user.preventAutoDelete');
                } else {
                    return null;
                }
            }
        }
    }

    public function updatePhoto($request)
    {
        if ($request->hasFile('file')) {
            $id = $request->id;
            $file = $request->file('file');
            $name = $file->getClientOriginalName();
            $file->move('pictures/', $name);
        }

        User::where('id', $id)
            ->update(['picture' => $name]);

        return User::find($id);
    }
}
