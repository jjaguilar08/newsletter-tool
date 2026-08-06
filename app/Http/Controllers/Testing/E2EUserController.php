<?php

declare(strict_types=1);

namespace App\Http\Controllers\Testing;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Testing\StoreE2EUserRequest;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

// Exists solely so the frontend's Playwright E2E suite can create/tear down
// a real, dedicated staff user per spec via genuine HTTP calls, mirroring
// how every other fixture (subscribers, campaigns) is created - rather than
// reaching for `tinker`, which can't run unattended. There is no
// registration flow anywhere else in this app (see PROJECT_NOTES.md Day 21)
// - this is the one deliberate exception, and it only exists outside
// production (see RestrictToNonProduction).
class E2EUserController extends Controller
{
    public function store(StoreE2EUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->isEmpty() ? 'E2E Test User' : $request->input('name'),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'email_verified_at' => now(),
            'role' => UserRole::Staff,
        ]);

        return response()->json($user, 201);
    }

    // Deletes every campaign this user created before the user itself -
    // `campaigns.created_by` has no cascade-on-delete (a real user's
    // campaigns shouldn't vanish if their account is ever removed), so
    // deleting the user first would fail with a foreign key violation.
    // campaign_sends cascades from campaigns at the DB level already.
    // This also covers Sent campaigns a spec can't delete through the
    // normal authenticated API (CampaignPolicy::delete is Draft/Scheduled
    // only) - the E2E suite relies on this endpoint for that cleanup.
    public function destroy(User $user): JsonResponse
    {
        Campaign::where('created_by', $user->id)->delete();
        $user->delete();

        return response()->json(null, 204);
    }
}
