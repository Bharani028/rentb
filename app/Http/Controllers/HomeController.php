<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyType;

class HomeController extends Controller
{
public function index()
{
    $city = request('city'); // optional pre-filter

    $featured = Property::with(['type','media'])
        ->where('status', 'active')
        ->when($city, fn($q) => $q->where('city', $city))
        ->latest()
        ->take(12)
        ->get();

    // 🔥 Trending by highest views (tie-breaker: most recent)
    $trending = Property::with(['type','media'])
        ->where('status','active')
        ->orderByDesc('view_count')        // <-- use 'view_count' if that's your column
        ->orderByDesc('id')           // tie-breaker to keep it fresh
        ->take(12)
        ->get();

    $types = PropertyType::orderBy('name')->get(['id','name']);

    return view('home', compact('featured', 'trending', 'types', 'city'));
}

}
