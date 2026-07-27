<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SubscriberStatus;
use App\Http\Requests\Subscribers\ImportSubscribersRequest;
use App\Http\Requests\Subscribers\IndexSubscriberRequest;
use App\Http\Requests\Subscribers\StoreSubscriberRequest;
use App\Http\Requests\Subscribers\UpdateSubscriberRequest;
use App\Http\Resources\SubscriberResource;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
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
            // Explicit rather than relying on the migration's DB-level
            // default: Subscriber::create()'s in-memory model reflects only
            // what's passed here, so an omitted `status` would otherwise
            // leave $subscriber->status null until a fresh fetch - crashing
            // SubscriberResource's ->status->value.
            'status' => $request->validated('status') ?? SubscriberStatus::Subscribed,
            'unsubscribe_token' => Subscriber::generateUnsubscribeToken(),
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

        $data = $request->validated();

        // Compare against the model's status *before* $data is applied -
        // this is what makes it a transition check (re-saving the same
        // status is a no-op) rather than just reacting to whatever value
        // showed up in the request.
        if (array_key_exists('status', $data)) {
            $data = [
                ...$data,
                ...$this->timestampsForStatusTransition($subscriber->status, SubscriberStatus::from($data['status'])),
            ];
        }

        $subscriber->update($data);

        return new SubscriberResource($subscriber);
    }

    /**
     * @return array<string, mixed>
     */
    private function timestampsForStatusTransition(SubscriberStatus $from, SubscriberStatus $to): array
    {
        if ($from === $to) {
            return [];
        }

        return match ($to) {
            SubscriberStatus::Unsubscribed => ['unsubscribed_at' => now()],
            SubscriberStatus::Subscribed => ['subscribed_at' => now(), 'unsubscribed_at' => null],
            SubscriberStatus::Bounced => [],
        };
    }

    public function destroy(Subscriber $subscriber): Response
    {
        $this->authorize('delete', $subscriber);

        $subscriber->delete();

        return response()->noContent();
    }

    public function import(ImportSubscribersRequest $request): JsonResponse
    {
        $this->authorize('create', Subscriber::class);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $header = $header === false ? [] : array_map(fn ($column) => strtolower(trim((string) $column)), $header);
        $emailColumn = array_search('email', $header, true);
        $nameColumn = array_search('name', $header, true);

        if ($emailColumn === false) {
            fclose($handle);

            return response()->json(['message' => 'CSV file must have an "email" column.'], 422);
        }

        $created = 0;
        $updated = 0;
        $skipped = [];
        $rowNumber = 1; // Row 1 is the header, matching what a spreadsheet would show.

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Lowercased for the same reason as Store/UpdateSubscriberRequest:
            // email uniqueness must not depend on the DB connection's
            // collation (SQLite locally is case-sensitive by default; the
            // deployed engine is still undecided per Day 1's note).
            $email = Str::lower(trim((string) ($row[$emailColumn] ?? '')));
            $name = $nameColumn !== false ? trim((string) ($row[$nameColumn] ?? '')) : null;
            $name = $name === '' ? null : $name;

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped[] = ['row' => $rowNumber, 'reason' => 'Invalid email format.'];

                continue;
            }

            $subscriber = Subscriber::where('email', $email)->first();

            if ($subscriber) {
                if ($name !== null) {
                    $subscriber->update(['name' => $name]);
                }

                $updated++;

                continue;
            }

            Subscriber::create([
                'email' => $email,
                'name' => $name,
                'status' => SubscriberStatus::Subscribed,
                'subscribed_at' => now(),
                'unsubscribe_token' => Subscriber::generateUnsubscribeToken(),
            ]);

            $created++;
        }

        fclose($handle);

        return response()->json([
            'created' => $created,
            'updated' => $updated,
            'skipped' => count($skipped),
            'skipped_rows' => $skipped,
        ]);
    }
}
