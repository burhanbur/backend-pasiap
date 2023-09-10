<?php

namespace App\Http\Middleware;

use Closure;
use URL;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 3, $decayMinutes = 1)
    {
        $key = $this->getLoginAttemptsKey($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $response = new Response('tooManyAttempts', 429);
            $response->headers->set('Retry-After', $this->limiter->availableIn($key));

            if ($response->getContent() == 'tooManyAttempts') {
                $response = [
                    'success' => false,
                    'message' => 'Too many attempts',
                    'url' => URL::current()
                ];

                return response()->json($response, 429);
            }

            return $response;
        }

        $response = $next($request);

        if ($response->getStatusCode() != 200) {
            $this->limiter->hit($key, $decayMinutes * 60);
        } else {
            $this->limiter->clear($key);
        }

        return $response;
    }

    protected function getLoginAttemptsKey($request)
    {
        $username = $request->input('username');

        return 'login_attempts:' . Str::lower($username) . ':' . $request->ip();
    }
}
