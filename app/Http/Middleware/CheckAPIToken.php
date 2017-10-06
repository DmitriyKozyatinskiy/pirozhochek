<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;

class CheckAPIToken
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string|null  $guard
   * @return mixed
   */
  public function handle($request, Closure $next, $guard = null)
  {
    try {
      if (!$user = JWTAuth::parseToken()->authenticate()) {
        return response()->json([
          'status' => 401,
          'statusText' => 'User not found',
          'isSuccess' => false
        ], 401);
      }
    } catch (Exception $e) {
      return response()->json([
        'status' => 401,
        'statusText' => 'Unauthorized',
        'isSuccess' => false
      ], 401);
    }

    return $next($request);
  }
}
