<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Http\Requests\Campaigns\IndexCampaignRequest;
use App\Http\Requests\Campaigns\StoreCampaignRequest;
use App\Http\Requests\Campaigns\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
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
}
