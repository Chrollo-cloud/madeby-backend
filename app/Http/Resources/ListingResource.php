<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->slug,
            'databaseId' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'category' => $this->category->name,
            'category_id' => $this->category_id,
            'creatorId' => $this->creator->slug,
            'creator_id' => $this->creator_id,
            'price' => $this->price,
            'priceFrom' => $this->price_from,
            'stock' => $this->stock,
            'commissionSlots' => $this->commission_slots,
            'deliveryTime' => $this->delivery_time,
            'rating' => (float) $this->rating,
            'description' => $this->description,
            'image' => asset('storage/'.$this->image),
            'images' => $this->images->pluck('path')->map(fn ($path) => asset('storage/'.$path))->values(),
            'gallery' => $this->images->map(fn ($image) => ['id' => $image->id, 'url' => asset('storage/'.$image->path)]),
            'tags' => $this->tags ?? [],
        ];
    }
}
