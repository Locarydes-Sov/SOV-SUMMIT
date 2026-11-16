<?php

namespace App\Http\Middleware;

use App\Support\Seo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends "www." visitors to the canonical domain with a permanent (301) redirect,
 * keeping the path and query string.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = (string) parse_url(Seo::baseUrl(), PHP_URL_HOST);

        if ($canonicalHost !== '' && $request->getHost() === 'www.'.$canonicalHost) {
            return redirect()->to(Seo::baseUrl().$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
