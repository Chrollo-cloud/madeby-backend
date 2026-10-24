<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ListingManager
{
    public function data(Request $request, ?Listing $listing = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'type' => ['required', Rule::in(['product', 'commission'])],
            'category_id' => 'required|exists:categories,id',
            'creator_id' => 'required|exists:creators,id',
            'description' => 'required|string',
            'rating' => 'required|numeric|min:0|max:5',
            'tags' => 'nullable|string',
            'image' => ($listing ? 'nullable' : 'required').'|image|mimes:jpg,jpeg,png|max:2048',
            'images' => 'nullable|array|max:20',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => [
                'integer',
                Rule::exists('listing_images', 'id')->where('listing_id', $listing?->id ?? 0),
            ],
        ];
        if ($request->type === 'product') {
            $rules += [
                'price' => 'required|integer|min:0|max:4294967295',
                'stock' => 'required|integer|min:0|max:4294967295',
            ];
        }
        if ($request->type === 'commission') {
            $rules += [
                'price_from' => 'required|integer|min:0|max:4294967295',
                'commission_slots' => 'required|integer|min:0|max:4294967295',
                'delivery_time' => 'required|string|max:255',
            ];
        }
        $data = $request->validate($rules);
        $data['slug'] = Str::slug($data['title']);
        $base = $data['slug'];
        $number = 2;
        while (Listing::where('slug', $data['slug'])->when($listing, fn ($q) => $q->where('id', '!=', $listing->id))->exists()) {
            $data['slug'] = $base.'-'.$number++;
        }
        $data['tags'] = isset($data['tags']) && $data['tags'] !== '' ? array_values(array_filter(array_map('trim', explode(',', $data['tags'])))) : null;
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('listings', 'public');
        } else {
            unset($data['image']);
        }
        if ($data['type'] === 'product') {
            $data['price_from'] = null;
            $data['commission_slots'] = null;
            $data['delivery_time'] = null;
        } else {
            $data['price'] = null;
            $data['stock'] = null;
        }
        unset($data['images'], $data['remove_images']);

        return $data;
    }

    public function gallery(Request $request, Listing $listing): void
    {
        foreach ($listing->images()->whereIn('id', $request->input('remove_images', []))->get() as $removed) {
            $path = $removed->path;
            $removed->delete();
            $this->deleteUnused($path);
        }
        foreach ($request->file('images', []) as $image) {
            $listing->images()->create(['path' => $image->store('listings', 'public')]);
        }
    }

    public function deleteUnused(?string $path): void
    {
        if ($path && ! Listing::where('image', $path)->exists()
            && ! ListingImage::where('path', $path)->exists()
            && ! Creator::where('avatar', $path)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }

    public function destroy(Listing $listing): void
    {
        $paths = array_merge([$listing->image], $listing->images()->pluck('path')->all());
        $listing->delete();
        foreach ($paths as $path) {
            $this->deleteUnused($path);
        }
    }
}
