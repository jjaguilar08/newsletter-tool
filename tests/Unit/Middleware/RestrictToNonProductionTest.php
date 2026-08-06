<?php

declare(strict_types=1);

use App\Http\Middleware\RestrictToNonProduction;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

// Needs the real Laravel app (not Pest's plain-PHPUnit Unit default) so the
// response()/app() helpers this middleware and test rely on actually work -
// RefreshDatabase isn't pulled in since nothing here touches the DB.
uses(TestCase::class);

// A direct unit test of the middleware, not a full HTTP feature test -
// forcing app()->detectEnvironment('production') for a Feature test breaks
// an unrelated thing (Laravel's CSRF middleware only skips itself when the
// app environment is 'testing', so flipping it to 'production' mid-test
// turned a real request into a 419 CSRF failure before it ever reached this
// middleware). Testing the middleware's own handle() directly sidesteps
// that entirely.
test('lets a request through outside production', function () {
    app()->detectEnvironment(fn () => 'local');

    $middleware = new RestrictToNonProduction;
    $request = Request::create('/api/testing/users', 'POST');

    $response = $middleware->handle($request, fn ($req) => response('ok'));

    expect($response->getContent())->toBe('ok');
});

test('404s the request in production', function () {
    app()->detectEnvironment(fn () => 'production');

    $middleware = new RestrictToNonProduction;
    $request = Request::create('/api/testing/users', 'POST');

    expect(fn () => $middleware->handle($request, fn ($req) => response('ok')))
        ->toThrow(NotFoundHttpException::class);
});
