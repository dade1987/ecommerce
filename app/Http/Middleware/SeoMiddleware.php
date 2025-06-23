<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Canonicalize www to non-www
        if (str_starts_with($request->getHost(), 'www.')) {
            $newHost = substr($request->getHost(), 4);
            return redirect()->to($request->path(), 301, $request->header(), $request->isSecure())
                ->setHost($newHost);
        }

        $response = $next($request);

        // Add HSTS header
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
