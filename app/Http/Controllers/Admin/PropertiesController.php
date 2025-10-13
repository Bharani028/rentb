<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PropertiesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $q = $request->string('q')->toString();
            $status = $request->string('status')->toString();
            $city = $request->string('city')->toString();

            $query = Property::query()
                ->with(['user', 'media'])
                ->when($q, function (Builder $query) use ($q) {
                    $query->where(function (Builder $w) use ($q) {
                        $w->where('title', 'like', "%{$q}%")
                          ->orWhere('city', 'like', "%{$q}%")
                          ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
                    });
                })
                ->when($status, fn (Builder $qb) => $qb->where('status', $status))
                ->when($city, fn (Builder $qb) => $qb->where('city', $city))
                ->latest();

            return DataTables::of($query)
                ->addColumn('thumbnail', function ($property) {
                    $thumb = $property->getFirstMediaUrl('images', 'thumb') ?: $property->getFirstMediaUrl('images');
                    return $thumb
                        ? '<img src="' . $thumb . '" alt="thumb" class="thumb">'
                        : '<div class="thumb d-flex align-items-center justify-content-center subtle"><i class="bi bi-card-image"></i></div>';
                })
                ->addColumn('title_link', function ($property) {
                    $title = $property->title ?? 'Property #' . $property->id;
                    return '<a href="' . route('properties.show', $property->id) . '" class="text-decoration-none" target="_blank" rel="noopener">' . $title . '</a>';
                })
                ->addColumn('user_name', function ($property) {
                    return $property->user->name ?? '—';
                })
                ->addColumn('city_name', function ($property) {
                    return $property->city ?? '—';
                })
                ->addColumn('created', function ($property) {
                    return $property->created_at ? $property->created_at->format('d M Y') : '—';
                })
                ->addColumn('status_badge', function ($property) {
                    $status = $property->status ?? 'pending';
                    $class = $status === 'active' ? 'status-active' : ($status === 'rejected' ? 'status-rejected' : 'status-pending');
                    return '<span class="status-badge ' . $class . '">' . ucfirst($status) . '</span>';
                })
                ->addColumn('actions', function ($property) {
                    $status = $property->status ?? 'pending';
                    $buttons = '<div class="btn-group">';
                    $buttons .= '<a href="' . route('properties.show', $property->id) . '" class="btn btn-sm btn-outline-secondary" title="View" target="_blank" rel="noopener"><i class="bi bi-eye"></i></a>';
                    if ($status === 'pending') {
                        $buttons .= '<button class="btn btn-sm btn-success js-approve" data-action="' . route('admin.properties.approve', $property->id) . '" data-title="' . ($property->title ?? 'Property #' . $property->id) . '"><i class="bi bi-check2"></i></button>';
                        $buttons .= '<button class="btn btn-sm btn-outline-danger js-reject" data-action="' . route('admin.properties.reject', $property->id) . '" data-title="' . ($property->title ?? 'Property #' . $property->id) . '"><i class="bi bi-x-lg"></i></button>';
                    } elseif ($status === 'active') {
                        $buttons .= '<button class="btn btn-sm btn-outline-danger js-reject" data-action="' . route('admin.properties.reject', $property->id) . '" data-title="' . ($property->title ?? 'Property #' . $property->id) . '"><i class="bi bi-x-octagon"></i></button>';
                    } elseif ($status === 'rejected') {
                        $buttons .= '<button class="btn btn-sm btn-outline-success js-approve" data-action="' . route('admin.properties.approve', $property->id) . '" data-title="' . ($property->title ?? 'Property #' . $property->id) . '"><i class="bi bi-check2-circle"></i></button>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['thumbnail', 'title_link', 'status_badge', 'actions'])
                ->make(true);
        }

        // Distinct cities for filter
        $cities = Property::query()
            ->select('city')
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->toArray();

        return view('admin.properties.index', compact('cities'));
    }

    public function approve(Property $property)
    {
        $property->status = 'active';
        $property->rejection_reason = null;
        $property->approved_at = now();
        $property->approved_by = Auth::id();
        $property->save();

        return back()->with('success', 'Property approved.');
    }

    public function reject(Request $request, Property $property)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $property->status = 'rejected';
        $property->rejection_reason = $data['reason'] ?? 'Rejected by admin';
        $property->approved_at = null;
        $property->approved_by = null;
        $property->save();

        return back()->with('success', 'Property rejected.');
    }
}