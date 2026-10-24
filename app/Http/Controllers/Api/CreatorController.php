<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorResource;
use App\Models\Creator;
use App\Services\ListingManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreatorController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['per_page' => 'nullable|integer|min:1|max:100', 'page' => 'nullable|integer|min:1', 'search' => 'nullable|string|max:255']);

        return CreatorResource::collection(Creator::withCount('listings')
            ->when($data['search'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('id')->paginate($data['per_page'] ?? 20)->withQueryString());
    }

    public function show(Creator $creator)
    {
        return new CreatorResource($creator->loadCount('listings'));
    }

    private function data(Request $request, ?Creator $creator = null)
    {
        $data = $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('creators')->ignore($creator?->id)],
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'bio' => 'required|string',
            'location' => 'required|string|max:255',
            'followers' => 'required|string|max:255',
            'likes' => 'required|string|max:255',
            'avatar' => ($creator ? 'nullable' : 'required').'|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('creators', 'public');
        } else {
            unset($data['avatar']);
        }

        return $data;
    }

    public function store(Request $request)
    {
        return (new CreatorResource(Creator::create($this->data($request))))->response()->setStatusCode(201);
    }

    public function update(Request $request, Creator $creator, ListingManager $manager)
    {
        $old = $creator->avatar;
        $creator->update($this->data($request, $creator));
        if ($request->hasFile('avatar')) {
            $manager->deleteUnused($old);
        }

        return new CreatorResource($creator);
    }

    public function destroy(Creator $creator, ListingManager $manager)
    {
        abort_if($creator->listings()->exists(), 409, 'Remove or reassign this creator’s listings first.');
        $path = $creator->avatar;
        $creator->delete();
        $manager->deleteUnused($path);

        return response()->noContent();
    }
}
