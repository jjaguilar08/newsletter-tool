<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Http\Requests\Campaigns\IndexCampaignRequest;
use App\Http\Requests\Campaigns\ScheduleCampaignRequest;
use App\Http\Requests\Campaigns\StoreCampaignRequest;
use App\Http\Requests\Campaigns\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CampaignSendResource;
use App\Jobs\SendCampaignJob;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

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
        if (! Campaign::claimForSending($campaign->id, CampaignStatus::Draft)) {
            return response()->json(['message' => 'Only a draft campaign can be sent.'], 409);
        }

        $campaign = $campaign->fresh();

        SendCampaignJob::dispatch($campaign);

        return response()->json([
            'message' => 'Campaign send started.',
            'data' => new CampaignResource($campaign),
        ]);
    }

    public function schedule(ScheduleCampaignRequest $request, Campaign $campaign): JsonResponse
    {
        $this->authorize('schedule', $campaign);

        // Same atomic-update guard as send(), but this transition (Draft ->
        // Scheduled) is only ever reached from Draft here, so it doesn't
        // share the claimForSending() helper - that one's specifically for
        // the "claim for sending" step both send() and the scheduled
        // dispatcher perform, from different source statuses.
        $updated = Campaign::whereKey($campaign->id)
            ->where('status', CampaignStatus::Draft)
            ->update([
                'status' => CampaignStatus::Scheduled,
                'scheduled_at' => Carbon::parse($request->validated('scheduled_at')),
            ]);

        if ($updated === 0) {
            return response()->json(['message' => 'Only a draft campaign can be scheduled.'], 409);
        }

        $campaign = $campaign->fresh();

        return response()->json([
            'message' => 'Campaign scheduled.',
            'data' => new CampaignResource($campaign),
        ]);
    }

    public function sends(Campaign $campaign): AnonymousResourceCollection
    {
        $this->authorize('view', $campaign);

        $sends = $campaign->campaignSends()
            ->with('subscriber')
            ->paginate(15)
            ->withQueryString();

        return CampaignSendResource::collection($sends);
    }

    public function defaultTemplate(): JsonResponse
    {
        // No Campaign instance exists yet (this is fetched before a
        // campaign's editor has any content), so it authorizes against the
        // class the same way CampaignAssetController's upload does.
        $this->authorize('create', Campaign::class);

        return response()->json([
            'html' => View::make('emails.campaign-default-template')->render(),
        ]);
    }

    public function preview(Campaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);

        // A placeholder, unsaved Subscriber stands in for the real
        // recipient: preview shouldn't depend on any subscriber existing,
        // and must never leak a real unsubscribe token.
        $placeholder = new Subscriber(['unsubscribe_token' => 'preview-unsubscribe-token']);

        $mail = new CampaignMail($campaign, $placeholder);

        return response()->json([
            'subject' => $campaign->subject,
            'html' => $mail->render(),
        ]);
    }
}
