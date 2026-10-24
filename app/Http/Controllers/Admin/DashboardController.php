<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\Listing;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', ['title' => 'Admin dashboard', 'total' => Listing::count(), 'products' => Listing::where('type', 'product')->count(), 'commissions' => Listing::where('type', 'commission')->count(), 'creatorsCount' => Creator::count(), 'recent' => Listing::with('creator', 'category')->latest()->take(5)->get()]);
    }
}
