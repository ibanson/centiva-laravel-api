<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class BrokerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
      return [
          'id' => $this->id,
          'name'     => $this->name,
          'email'    => $this->email,
          'team'   => new TeamResource($this->whenLoaded('team')),
      ];
    }
}
