<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Campaigns\UploadCampaignAssetRequest;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CampaignAssetController extends Controller
{
    public function store(UploadCampaignAssetRequest $request): JsonResponse
    {
        // No Campaign instance exists yet at upload time (the design editor
        // uploads images while composing, before the campaign is saved), so
        // this authorizes against the class the same way CampaignPolicy's
        // own create() does - staff-only, no specific row to check.
        $this->authorize('create', Campaign::class);

        $path = $request->file('image')->store('campaign-assets', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
