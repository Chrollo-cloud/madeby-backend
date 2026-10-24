<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use App\Services\ListingManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    public function __construct(private ListingManager $manager)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(['product', 'commission'])],
            'search' => 'nullable|string|max:255',
            'creator' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
        $query = Listing::with('creator', 'category', 'images');
        $type = $request->routeIs('api.products.*') ? 'product'
            : ($request->routeIs('api.commissions.*') ? 'commission' : ($filters['type'] ?? null));
        $query->when($type, fn ($q) => $q->where('type', $type))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('tags', 'like', "%{$term}%")
                    ->orWhereHas('creator', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            }))
            ->when($filters['creator'] ?? null, fn ($q, $slug) => $q->whereHas('creator', fn ($q) => $q->where('slug', $slug)))
            ->when($filters['category'] ?? null, fn ($q, $name) => $q->whereHas('category', fn ($q) => $q->where('name', $name)));

        return ListingResource::collection($query->orderBy('id')->paginate($filters['per_page'] ?? 20)->withQueryString());
    }

    public function show(Request $request, Listing $listing)
    {
        if ($request->routeIs('api.products.*')) {
            abort_unless($listing->type === 'product', 404);
        }
        if ($request->routeIs('api.commissions.*')) {
            abort_unless($listing->type === 'commission', 404);
        }

        return new ListingResource($listing->load('creator', 'category', 'images'));
    }

    public function store(Request $request)
    {
        $listing = Listing::create($this->manager->data($request));
        $this->manager->gallery($request, $listing);

        return (new ListingResource($listing->load('creator', 'category', 'images')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Listing $listing)
    {
        $data = $this->manager->data($request, $listing);
        $oldImage = $listing->image;
        $listing->update($data);
        $this->manager->gallery($request, $listing);
        if ($request->hasFile('image')) {
            $this->manager->deleteUnused($oldImage);
        }

        return new ListingResource($listing->load('creator', 'category', 'images'));
    }

    public function destroy(Listing $listing)
    {
        $this->manager->destroy($listing);

        return response()->noContent();
    }
}
