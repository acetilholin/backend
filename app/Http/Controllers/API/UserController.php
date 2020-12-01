<?php

namespace App\Http\Controllers\API;

use App\Helpers\MsgFormatterHelper;
use App\Helpers\UserHelper;
use App\Http\Resources\UsersResource;
use App\Notifications\User as UserNotification;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return UsersResource::collection(User::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = array(
            'username' => 'required|unique:users|max:30',
            'email' => 'required|unique:users|email:rfc,dns'
        );

        $userData = request(['username', 'email']);

        $validator = Validator::make($userData, $rules, $this->messages());


        if ($validator->fails()) {
            $formatter = new MsgFormatterHelper();
            $messages = $formatter->format($validator->errors()->all());
            return response()->json(['error' => $messages], 422);
        } else {
            $notEncrypted = Str::random(20);
            $password = Hash::make($notEncrypted);

            $userData['password'] = $password;
            $userData['enabled'] = 1;
            $userData['last_seen'] = date("Y-m-d");

            try {
                $user = User::create($userData);
                $user->notify(new UserNotification($userData['email'], $notEncrypted));

                return response()->json([
                    'success' => trans('user.userCreated')
                ], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => trans('user.cannotCreate')], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);

        $currentUserData = auth()->user();
        $currentUser = $currentUserData->getAttributes();
        $uid = $currentUser['id'];

        $userHelper = new UserHelper();
        $status = $userHelper->checkPermissions('edit', $user, $uid);

        if ($status) {
            return response()->json(['error' => $status], 422);
        }

        $userHelper->editSingleAttr($request);

        return response()->json([
            'success' => trans('user.userDetailChanged')
        ], 200);
    }

    /**
     * Show the form for editing password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newPassword(Request $request)
    {
        $id = $request->id;
        $oldPassword = $request->password_old;
        $password = $request->password;
        $password_confirmation = $request->password_new;

        $rules = array(
            'password' => 'required|min:5',
            'password_confirmation' => 'required|same:password'
        );

        $credentials = [
            'password' => $password,
            'password_confirmation' => $password_confirmation
        ];

        $validator = Validator::make($credentials, $rules, $this->messages());

        if ($validator->fails()) {
            $formatter = new MsgFormatterHelper();
            $messages = $formatter->format($validator->errors()->all());
            return response()->json(['error' => $messages], 422);
        }

        $userHelper = new UserHelper();
        if (!$userHelper->checkPassword($id, $oldPassword)) {
            return response()->json(['error' => trans('user.wrongOldPassword')], 422);
        } else {
            $userHelper->updatePassword($id, $password);
            $user = User::find($id);
            return response()->json([
                'success' => trans('user.passwordUpdated'),
                'user' => $user
            ], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {

    }

    /**
     * Update the specified photo in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function photo(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => trans('user.noPicture')], 422);
        }

        $userHelper = new UserHelper();
        $user = $userHelper->updatePhoto($request);

        return response()->json([
            'success' => trans('user.pictureUpdated'),
            'user' => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $userHelper = new UserHelper();

        $currentUserData = auth()->user();
        $currentUser = $currentUserData->getAttributes();
        $uid = $currentUser['id'];

        $status = $userHelper->checkPermissions('delete', $user, $uid);

        if ($status) {
            return response()->json(['error' => $status], 422);
        }

        try {
            $user->delete();
            return response()->json([
                'success' => trans('user.userDeleted'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => trans('user.cannotDelete')], 422);
        }
    }

    protected function messages()
    {
        return [
            'email.required' => trans('loginRegister.emailRequired'),
            'email.unique' => trans('loginRegister.emailAlreadyInUse'),
            'email.email' => trans('loginRegister.emailFormat'),
            'username.required' => trans('loginRegister.usernameRequired'),
            'username.unique' => trans('loginRegister.usernameAlreadyInUse'),
            'username.max' => trans('loginRegister.usernameMax'),
            'password.required' => trans('loginRegister.passwordRequired'),
            'password.min' => trans('loginRegister.passwordTooShort'),
            'password_confirmation.same' => trans('loginRegister.passwordMissmatch'),
            'password_confirmation.required' => trans('loginRegister.passConfirmationRequired'),
        ];
    }
}
