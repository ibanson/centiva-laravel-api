<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\TeamResource;
use App\Models\Team;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with('brokers')->get();

        return response()->json([
            'success' => true,
            'data' => TeamResource::collection($teams),
        ]);
    }

    public function show(Team $team)
    {
        return response()->json([
            'success' => true,
            'data' => new TeamResource($team->load('brokers')),
        ]);
    }

    /**
     * Soft delete a team and its brokers.
     */
    public function destroy(Team $team)
    {
        $team->brokers()->delete();
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team and associated brokers were soft deleted.',
        ]);
    }
}
