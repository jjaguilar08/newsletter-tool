<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Http\Requests\Campaigns\IndexCampaignRequest;
use App\Http\Requests\Campaigns\StoreCampaignRequest;
use App\Http\Requests\Campaigns\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CampaignController extends Controller
{
    public function index(IndexCampaignRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Campaign::class);

        $campaigns = Campaign::query()
            ->when($request->validated('status'), fn ($query, $status) => $query->where('status', $status))
            ->paginate(15)
            ->withQueryString();

        return CampaignResource::collection($campaigns);
    }

    public function store(StoreCampaignRequest $request): CampaignResource
    {
        $this->authorize('create', Campaign::class);

        $campaign = Campaign::create([
            ...$request->validated(),
            'status' => CampaignStatus::Draft,
            'created_by' => $request->user()->id,
        ]);

        return new CampaignResource($campaign);
    }

    public function show(Campaign $campaign): CampaignResource
    {
        $this->authorize('view', $campaign);

        return new CampaignResource($campaign);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): CampaignResource
    {
        $this->authorize('update', $campaign);

        $campaign->update($request->validated());

        return new CampaignResource($campaign);
    }

    public function destroy(Campaign $campaign): Response
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        return response()->noContent();
    }

    public function send(Campaign $campaign): JsonResponse
    {
        $this->authorize('send', $campaign);

        // Atomic conditional update, not a read-then-write check: this is
        // what actually closes the race window, since two concurrent
        // requests can't both flip a row from Draft to Sending.
        $updated = Campaign::whereKey($campaign->id)
            ->where('status', CampaignStatus::Draft)
            ->update(['status' => CampaignStatus::Sending]);

        if ($updated === 0) {
            return response()->json(['message' => 'Only a draft campaign can be sent.'], 409);
        }

        $campaign = $campaign->fresh();

        SendCampaignJob::dispatch($campaign);

        return response()->json([
            'message' => 'Campaign send started.',
            'data' => new CampaignResource($campaign),
        ]);
    }
}
