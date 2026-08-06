<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Gates the /api/testing/* routes (E2E fixture setup/teardown) - 404s
// rather than 403s so the routes' existence isn't revealed in production
// at all. There is no other authorization on these routes (they run
// before any session exists, to create the very user a test logs in as),
// so this environment check is the only thing standing between them and
// the open internet if ever deployed with APP_ENV=production.
class RestrictToNonProduction
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(! app()->isProduction(), 404);

        return $next($request);
    }
}
