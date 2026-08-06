<?php

declare(strict_types=1);

use App\Mail\SampleNewsletterMail;
use Illuminate\Support\Facades\Mail;

test('a visitor can request a sample newsletter without authenticating', function () {
    Mail::fake();

    $response = $this
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
        ->postJson('/api/marketing/sample-newsletter', ['email' => 'visitor@example.com']);

    $response->assertOk();
    $response->assertExactJson([
        'message' => 'If that address is valid, a sample newsletter is on its way.',
    ]);

    Mail::assertSent(SampleNewsletterMail::class, function (SampleNewsletterMail $mail) {
        return $mail->hasTo('visitor@example.com');
    });
});

test('email is required', function () {
    Mail::fake();

    $response = $this
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])
        ->postJson('/api/marketing/sample-newsletter', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('email');

    Mail::assertNothingSent();
});

test('email must be a valid email address', function () {
    Mail::fake();

    $response = $this
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.12'])
        ->postJson('/api/marketing/sample-newsletter', ['email' => 'not-an-email']);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('email');

    Mail::assertNothingSent();
});

test('a 4th request from the same IP within an hour is rate limited', function () {
    Mail::fake();

    $makeRequest = fn () => $this
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.13'])
        ->postJson('/api/marketing/sample-newsletter', ['email' => 'visitor@example.com']);

    $makeRequest()->assertOk();
    $makeRequest()->assertOk();
    $makeRequest()->assertOk();
    $makeRequest()->assertStatus(429);

    Mail::assertSentCount(3);
});
