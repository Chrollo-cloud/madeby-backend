<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Creator;
use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(private \App\Services\ListingManager $manager)
    {
    }

    public function index(Request $request)
    {
        $listings = Listing::with('creator', 'category')->when($request->s, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))->when($request->id_creator, fn ($q, $id) => $q->where('creator_id', $id))->when($request->type, fn ($q, $type) => $q->where('type', $type))->latest()->paginate(5)->appends($request->query());

        return view('admin.listings', compact('listings') + ['title' => 'Listings', 'creators' => Creator::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.input-listing', ['title' => 'New listing', 'categories' => Category::all(), 'creators' => Creator::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $listing = Listing::create($this->manager->data($request));
        $this->manager->gallery($request, $listing);

        return redirect()->route('admin.listings.index')->with('success', 'Listing created.');
    }

    public function edit(Listing $listing)
    {
        return view('admin.input-listing', ['title' => 'Edit listing', 'listing' => $listing, 'categories' => Category::all(), 'creators' => Creator::orderBy('name')->get()]);
    }

    public function update(Request $request, Listing $listing)
    {
        $data = $this->manager->data($request, $listing);
        $oldImage = $listing->image;
        $listing->update($data);
        if ($request->hasFile('image')) {
            $this->manager->deleteUnused($oldImage);
        } $this->manager->gallery($request, $listing);

        return redirect()->route('admin.listings.index')->with('success', 'Listing updated.');
    }

    public function destroy(Listing $listing)
    {
        $this->manager->destroy($listing);

        return redirect()->route('admin.listings.index')->with('success', 'Listing and its images deleted.');
    }

    public function creator($id)
    {
        $creator = Creator::findOrFail($id);
        $listings = $creator->listings()->with('creator', 'category')->latest()->paginate(5)->withQueryString();

        return view('admin.listings', ['title' => "{$creator->name}'s listings", 'listings' => $listings, 'creators' => Creator::orderBy('name')->get(), 'selectedCreator' => $creator]);
    }
}
