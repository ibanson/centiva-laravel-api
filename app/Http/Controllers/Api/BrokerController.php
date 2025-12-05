<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBrokerRequest;
use App\Http\Resources\Api\BrokerResource;
use App\Models\Broker;
use App\Models\Team;

class BrokerController extends Controller
{
    public function index()
    {
        $brokers = Broker::with('team')->get();

        return response()->json([
            'success' => true,
            'data' => BrokerResource::collection($brokers),
        ]);
    }

    public function store(StoreBrokerRequest $request, Team $team)
    {
        $broker = $team->brokers()->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new BrokerResource($broker),
        ], 201);
    }

    public function show(Broker $broker)
    {
        return response()->json([
            'success' => true,
            'data' => new BrokerResource($broker->load('team')),
        ]);
    }

}
