<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->slug,
            'databaseId' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'bio' => $this->bio,
            'location' => $this->location,
            'avatar' => asset('storage/'.$this->avatar),
            'followers' => $this->followers,
            'likes' => $this->likes,
            'works' => $this->listings_count ?? $this->listings()->count(),
        ];
    }
}
