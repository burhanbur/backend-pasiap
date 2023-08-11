<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

use Illuminate\Http\Request;

use App\Utilities\Response;

class VerifyUser
{
    use Response;

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->unauthorized();
        }

        if (!$user->email_verified_at) {
            return $this->notVerified();
        }

        return $next($request);
    }
}
