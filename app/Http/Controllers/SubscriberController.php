<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Subscribers\IndexSubscriberRequest;
use App\Http\Requests\Subscribers\StoreSubscriberRequest;
use App\Http\Requests\Subscribers\UpdateSubscriberRequest;
use App\Http\Resources\SubscriberResource;
use App\Models\Subscriber;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SubscriberController extends Controller
{
    public function index(IndexSubscriberRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Subscriber::class);

        $subscribers = Subscriber::query()
            ->when($request->validated('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->validated('search'), fn ($query, $search) => $query->where(
                fn ($query) => $query->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
            ))
            ->paginate(15)
            ->withQueryString();

        return SubscriberResource::collection($subscribers);
    }

    public function store(StoreSubscriberRequest $request): SubscriberResource
    {
        $this->authorize('create', Subscriber::class);

        $subscriber = Subscriber::create([
            ...$request->validated(),
            'unsubscribe_token' => Str::random(40),
            'subscribed_at' => now(),
        ]);

        return new SubscriberResource($subscriber);
    }

    public function show(Subscriber $subscriber): SubscriberResource
    {
        $this->authorize('view', $subscriber);

        return new SubscriberResource($subscriber);
    }

    public function update(UpdateSubscriberRequest $request, Subscriber $subscriber): SubscriberResource
    {
        $this->authorize('update', $subscriber);

        $subscriber->update($request->validated());

        return new SubscriberResource($subscriber);
    }

    public function destroy(Subscriber $subscriber): Response
    {
        $this->authorize('delete', $subscriber);

        $subscriber->delete();

        return response()->noContent();
    }
}
