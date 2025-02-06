<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckRealm
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(preg_match("/(logout|refresh|login|register|token|reset|me)/i", $request->getRequestUri())){
            return $next($request);
        }

        if ($request->headers->get('identifier')) {
            $user = User::where('identifier', $request->headers->get('identifier'))->first();

            if (!$user) {
                return response()->json(['error' => trans('loginRegister.wrongIdentifier')], 422);
            }

            $realm = $user->getAttributes()['realm'];
            $url = $request->url();

            preg_match('/\/api\/auth\/(r1|r2)\//', $url, $matches);

            if ($matches[1] !== $realm && $realm !== env('R_ALL')) {
                return response()->json(['error' => trans('loginRegister.insufficientRights')], 422);
            }

        } else {
            if (!$request->headers->get('identifier')) {
                return response()->json(['error' => trans('loginRegister.missingIdentifier')], 422);
            }
        }

        return $next($request);
    }
}
