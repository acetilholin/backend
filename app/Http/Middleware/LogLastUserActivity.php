<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class LogLastUserActivity
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
        $user = auth()->user();
        if ($user) {
            $expiresAt = Carbon::now()->addMinutes(5);
            Cache::put('user-is-online-' . auth()->user()->id, true, $expiresAt);
        }
        return $next($request);
    }
}
