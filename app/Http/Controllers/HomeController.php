<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Creator;
use App\Models\Listing;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontpage.index', ['title' => 'MADEBY — Student creativity', 'listings' => Listing::with('creator', 'category')->latest()->take(6)->get(), 'creators' => Creator::take(6)->get()]);
    }

    public function explore(Request $request)
    {
        $listings = Listing::with('creator', 'category')->when($request->s, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))->when($request->category, fn ($q, $category) => $q->whereHas('category', fn ($c) => $c->where('name', $category)))->when(in_array($request->type, ['product', 'commission']), fn ($q) => $q->where('type', $request->type))->latest()->paginate(6)->appends($request->query());

        return view('frontpage.explore', ['title' => 'Explore', 'listings' => $listings, 'categories' => Category::all()]);
    }

    public function show($slug)
    {
        return view('frontpage.show', ['title' => 'Listing', 'listing' => Listing::with('creator', 'category', 'images')->where('slug', $slug)->firstOrFail()]);
    }

    public function creators()
    {
        return view('frontpage.creators', ['title' => 'Creators', 'creators' => Creator::withCount('listings')->get()]);
    }

    public function creator($slug)
    {
        $creator = Creator::where('slug', $slug)->firstOrFail();

        return view('frontpage.creator', ['title' => $creator->name, 'creator' => $creator, 'listings' => $creator->listings()->with('category')->latest()->get()]);
    }

    public function becomeCreator()
    {
        return view('frontpage.become-creator', ['title' => 'Become a Creator']);
    }
}
