<?php

namespace App\Http\Middleware;

use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogBackendActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $response = $next($request);

        if ($response->getStatusCode() < 400) {
            ActivityLogger::logBackendRequest($request, $user);
        }

        return $response;
    }
}
