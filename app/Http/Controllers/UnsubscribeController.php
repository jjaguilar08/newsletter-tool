<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;

class UnsubscribeController extends Controller
{
    public function __invoke(string $token): JsonResponse
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->first();

        abort_if($subscriber === null, 404);

        if ($subscriber->status !== SubscriberStatus::Unsubscribed) {
            $subscriber->update([
                'status' => SubscriberStatus::Unsubscribed,
                'unsubscribed_at' => now(),
            ]);
        }

        return response()->json(['message' => 'You have been unsubscribed.']);
    }
}
