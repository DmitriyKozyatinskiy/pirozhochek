<?php

namespace App\Http\Middleware;

use Closure;

class ForceHTTPS
{

  public function handle($request, Closure $next)
  {
    if (!$request->secure() && env('APP_ENV') !== 'local') {
      return redirect()->secure($request->getRequestUri());
    }

    return $next($request);
  }
}