<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'name'      => $this->name,
            'brokers'   => BrokerResource::collection($this->whenLoaded('brokers')),
        ];
    }
}
