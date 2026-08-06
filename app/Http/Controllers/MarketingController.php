<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Marketing\SampleNewsletterRequest;
use App\Mail\SampleNewsletterMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MarketingController extends Controller
{
    public function sampleNewsletter(SampleNewsletterRequest $request): JsonResponse
    {
        // Failures (bad mailer config, transport outage) are swallowed
        // rather than surfaced - this is a public, unauthenticated endpoint,
        // so the response must stay generic regardless of what happened
        // internally.
        try {
            Mail::to($request->validated('email'))->send(new SampleNewsletterMail);
        } catch (Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'If that address is valid, a sample newsletter is on its way.',
        ]);
    }
}
