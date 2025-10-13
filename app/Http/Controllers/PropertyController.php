<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Property::query()
                ->with(['type', 'media'])
                ->where('user_id', auth()->id())
                ->select([
                    'id',
                    'title',
                    'property_type_id',
                    'price',
                    'rent_type',
                    'status',
                    'city',
                    'state',
                    'created_at'
                ]);

            if ($status = $request->get('status')) {
                $query->where('status', $status);
            }

            return DataTables::eloquent($query)
                ->addColumn('thumb', function (Property $property) {
                    $thumb = method_exists($property, 'getFirstMediaUrl')
                        ? $property->getFirstMediaUrl('images', 'thumb')
                        : 'https://placehold.co/48x48?text=🏠';
                    return '<div class="thumb overflow-hidden bg-light"><img src="' . e($thumb) . '" alt="thumb" style="width: 48px; height: 48px; object-fit: cover;"></div>';
                })
                ->addColumn('property', function (Property $property) {
                    $url = route('properties.show', $property->id);
                    $addr = collect([$property->city, $property->state])->filter()->implode(', ');
                    $title = e($property->title ?: 'Untitled Property');

                    return '<div class="fw-semibold">
                              <a href="'.$url.'" class="property-link" title="View property">'.$title.'</a>
                            </div>
                            <div class="text-muted small">'.e($addr ?: '—').'</div>';
                })
                ->addColumn('type', function (Property $property) {
                    return '<span class="badge bg-light text-dark fw-normal">' . e($property->type?->name ?? '—') . '</span>';
                })
                ->addColumn('price', function (Property $property) {
                    $price = $property->price ? number_format((float)$property->price, 2) : '0.00';
                    $per = $property->rent_type === 'daily' ? '/day' : '/mo';
                    return '<span class="fw-semibold">' . e($price) . ' <span class="text-muted">' . e($per) . '</span></span>';
                })
                ->addColumn('status_badge', function (Property $property) {
                    $status = strtolower($property->status ?? 'pending');
                    $map = [
                        'active'   => ['class' => 'accepted', 'label' => 'Active'],
                        'inactive' => ['class' => 'rejected', 'label' => 'Inactive'],
                        'pending'  => ['class' => 'pending',  'label' => 'Pending'],
                        'rejected' => ['class' => 'rejected', 'label' => 'Rejected'],
                    ];
                    $chip = $map[$status] ?? $map['pending'];
                    return '<span class="chip ' . $chip['class'] . ' text-capitalize"><span class="dot"></span>' . e($chip['label']) . '</span>';
                })
                ->addColumn('created', function (Property $property) {
                    return $property->created_at ? Carbon::parse($property->created_at)->format('d M Y') : '—';
                })
                ->addColumn('actions', function (Property $property) {
                    $activeDisabled = $property->status === 'active' ? 'disabled' : '';
                    $inactiveDisabled = $property->status === 'inactive' ? 'disabled' : '';
                    $showUrl = route('properties.show', $property->id);
                    $editUrl = route('properties.edit', $property->id);
                    $deleteUrl = route('properties.destroy', $property->id);
                    $activeUrl = route('properties.status', [$property->id, 'active']);
                    $inactiveUrl = route('properties.status', [$property->id, 'inactive']);
                    $csrf = csrf_token();

                    return '
                    <div class="d-flex align-items-center">
                        <form method="POST" class="js-confirm js-status-active d-inline m-0 me-1"
                              data-title="Set property to active?"
                              data-text="This will make the property visible to tenants."
                              action="' . $activeUrl . '">
                            <input type="hidden" name="_token" value="' . $csrf . '">
                            <input type="hidden" name="_method" value="PATCH">
                            <a class="btn-icon accept" href="#" onclick="this.closest(\'form\').submit(); return false;" ' . $activeDisabled . ' title="Set Active">
                                <i class="bi bi-check-circle"></i>
                            </a>
                        </form>
                        <form method="POST" class="js-confirm js-status-inactive d-inline m-0 me-1"
                              data-title="Set property to inactive?"
                              data-text="This will hide the property from tenants."
                              action="' . $inactiveUrl . '">
                            <input type="hidden" name="_token" value="' . $csrf . '">
                            <input type="hidden" name="_method" value="PATCH">
                            <a class="btn-icon reject" href="#" onclick="this.closest(\'form\').submit(); return false;" ' . $inactiveDisabled . ' title="Set Inactive">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </form>
                        <a href="' . $editUrl . '" class="btn-icon edit" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['thumb', 'property', 'type', 'price', 'status_badge', 'created', 'actions'])
                ->order(function ($query) use ($request) {
                    if ($request->input('order.0.column') !== null) {
                        $column = $request->input('order.0.column');
                        $direction = $request->input('order.0.dir', 'asc');

                        switch ($column) {
                            case 1:
                                $query->orderBy('title', $direction);
                                break;
                            case 2:
                                $query->orderBy('property_type_id', $direction);
                                break;
                            case 3:
                                $query->orderBy('price', $direction);
                                break;
                            case 4:
                                $query->orderBy('status', $direction);
                                break;
                            case 5:
                                $query->orderBy('created_at', $direction);
                                break;
                            default:
                                $query->orderBy('created_at', 'desc');
                                break;
                        }
                    } else {
                        $query->orderBy('created_at', 'desc');
                    }
                })
                ->toJson();
        }

        return view('dashboard');
    }

    public function create()
    {
        $types = PropertyType::all();
        $amenities = Amenity::all();
        return view('properties.create', compact('types', 'amenities'));
    }

public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'rent_type' => 'required|in:daily,monthly',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'kitchen' => 'nullable|integer|min:0',
            'balcony' => 'nullable|integer|min:0',
            'hall' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'parking' => 'nullable|boolean',
            'area' => 'nullable|numeric',
            'door_no' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date',
            'property_type_id' => 'required|exists:property_types,id',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240', // 10MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $property = new Property();
        $property->user_id = auth()->id();
        $property->property_type_id = $request->property_type_id;
        $property->title = $request->title;
        $property->description = $request->description;
        $property->price = $request->price;
        $property->rent_type = $request->rent_type;
        $property->bedrooms = $request->bedrooms ?? 0;
        $property->bathrooms = $request->bathrooms ?? 0;
        $property->kitchen = $request->kitchen ?? 0;
        $property->balcony = $request->balcony ?? 0;
        $property->hall = $request->hall ?? 0;
        $property->floors = $request->floors ?? 0;
        $property->area = $request->area;
        $property->door_no = $request->door_no;
        $property->street = $request->street;
        $property->district = $request->district;
        $property->city = $request->city;
        $property->state = $request->state;
        $property->country = $request->country;
        $property->postal_code = $request->postal_code;
        $property->phone_number = $request->phone_number;
        $property->available_from = $request->available_from;
        $property->available_to = $request->available_to;
        $property->status = 'pending'; // Default status
        $property->slug = \Str::slug($request->title) . '-' . \Str::random(5);
        $property->latitude = $request->latitude;
        $property->longitude = $request->longitude;

        // Check if "Parking" amenity is selected and set the parking column
        $parkingAmenityId = Amenity::where('name', 'Parking')->value('id');
        $property->parking = $request->has('amenities') && in_array($parkingAmenityId, $request->amenities) ? 1 : 0;

        $property->save();

        // Sync amenities
        if ($request->has('amenities')) {
            $property->amenities()->sync($request->amenities);
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $property->addMultipleMediaFromRequest(['images'])->each(function ($fileAdder) {
                $fileAdder->toMediaCollection('images');
            });
        }

        return redirect()->route('dashboard')->with('success', 'Property created successfully and awaiting approval.');
    }

public function show($id)
{
    $property = Property::with(['type', 'amenities', 'media', 'user'])->findOrFail($id);

    // Increment the views count for every visit
    $property->increment('view_count');

    $isOwner = auth()->check() && $property->user_id === auth()->id();

    if (!$isOwner && $property->status !== 'active') {
        abort(404);
    }

    return view('properties.show', compact('property', 'isOwner'));
}

    public function edit(Property $property)
    {
        if ($property->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit this property.');
        }

        $types = PropertyType::all();
        $amenities = Amenity::all();
        $property->load(['type', 'amenities', 'media']);

        return view('properties.edit', compact('property', 'types', 'amenities'));
    }

public function update(Request $request, Property $property)
    {
        if ($property->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to update this property.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'rent_type' => 'required|in:daily,monthly',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'kitchen' => 'nullable|integer|min:0',
            'balcony' => 'nullable|integer|min:0',
            'hall' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'parking' => 'nullable|boolean',
            'area' => 'nullable|numeric',
            'door_no' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            'available_from' => 'nullable|date',
            'available_to' => 'nullable|date',
            'property_type_id' => 'required|exists:property_types,id',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|max:10240', // 10MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $property->property_type_id = $request->property_type_id;
        $property->title = $request->title;
        $property->description = $request->description;
        $property->price = $request->price;
        $property->rent_type = $request->rent_type;
        $property->bedrooms = $request->bedrooms ?? 0;
        $property->bathrooms = $request->bathrooms ?? 0;
        $property->kitchen = $request->kitchen ?? 0;
        $property->balcony = $request->balcony ?? 0;
        $property->hall = $request->hall ?? 0;
        $property->floors = $request->floors ?? 0;
        $property->area = $request->area;
        $property->door_no = $request->door_no;
        $property->street = $request->street;
        $property->district = $request->district;
        $property->city = $request->city;
        $property->state = $request->state;
        $property->country = $request->country;
        $property->postal_code = $request->postal_code;
        $property->phone_number = $request->phone_number;
        $property->available_from = $request->available_from;
        $property->available_to = $request->available_to;
        $property->status = $property->status; // Keep existing status
        $property->slug = \Str::slug($request->title) . '-' . \Str::random(5);
        $property->latitude = $request->latitude;
        $property->longitude = $request->longitude;

        // Check if "Parking" amenity is selected and update the parking column
        $parkingAmenityId = Amenity::where('name', 'Parking')->value('id');
        $property->parking = $request->has('amenities') && in_array($parkingAmenityId, $request->amenities) ? 1 : 0;

        $property->save();

        // Sync amenities
        if ($request->has('amenities')) {
            $property->amenities()->sync($request->amenities);
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $property->clearMediaCollection('images'); // Optional: Clear existing images if desired
            $property->addMultipleMediaFromRequest(['images'])->each(function ($fileAdder) {
                $fileAdder->toMediaCollection('images');
            });
        }

        return redirect()->route('properties.show', $property->id)->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property)
    {
        if ($property->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to delete this property.');
        }

        $property->delete();
        return redirect()->route('dashboard')->with('success', 'Property deleted.');
    }

    public function updateStatus(Property $property, string $status)
    {
        if ($property->user_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to update this property status.');
        }

        if (!in_array($status, ['active', 'inactive'])) {
            return redirect()->route('dashboard')->with('error', 'Invalid status.');
        }

        // Restrict status changes for 'pending' properties to admin role only
        if ($property->status === 'pending' && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'Only admins can change the status of a pending property.');
        }

        try {
            $property->update(['status' => $status]);
            return redirect()->route('dashboard')->with('success', 'Property status updated to ' . $status . '.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('dashboard')->with('error', 'Could not update property status.');
        }
    }

    public function browse(Request $request)
    {
        $q = Property::query()
            ->with(['type', 'media'])
            ->when($request->filled('q'), fn ($qq) =>
                $qq->where(function ($w) use ($request) {
                    $term = '%' . $request->q . '%';
                    $w->where('title', 'like', $term)
                      ->orWhere('city', 'like', $term)
                      ->orWhere('state', 'like', $term)
                      ->orWhere('country', 'like', $term)
                      ->orWhere('description', 'like', $term);
                })
            )
            ->when($request->filled('type'), fn ($qq) =>
                $qq->whereIn('property_type_id', (array)$request->type)
            )
            ->when($request->filled('amenities'), fn ($qq) =>
                $qq->whereHas('amenities', fn ($a) => $a->whereIn('amenities.id', (array)$request->amenities))
            )
            ->when($request->filled('bedrooms') && $request->bedrooms !== 'any', fn ($qq) =>
                $qq->where('bedrooms', '>=', (int) $request->bedrooms)
            )
            ->when($request->filled('bathrooms') && $request->bathrooms !== 'any', fn ($qq) =>
                $qq->where('bathrooms', '>=', (int) $request->bathrooms)
            )
            ->when($request->filled('rent_type') && in_array($request->rent_type, ['daily', 'monthly']), fn ($qq) =>
                $qq->where('rent_type', $request->rent_type)
            )
            ->when($request->filled('min_price'), fn ($qq) =>
                $qq->where('price', '>=', (float) $request->min_price)
            )
            ->when($request->filled('max_price'), fn ($qq) =>
                $qq->where('price', '<=', (float) $request->max_price)
            )
            ->where('status', 'active');

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_low'  => $q->orderBy('price', 'asc'),
            'price_high' => $q->orderBy('price', 'desc'),
            'oldest'     => $q->orderBy('created_at', 'asc'),
            default      => $q->orderBy('created_at', 'desc'),
        };

        $properties = $q->paginate(12)->withQueryString();
        $types = PropertyType::orderBy('name')->get(['id', 'name']);
        $amenities = Amenity::orderBy('name')->get(['id', 'name', 'icon']);

        return view('properties.browse', compact('properties', 'types', 'amenities', 'sort'));
    }
}