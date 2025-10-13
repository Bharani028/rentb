<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{
    /**
     * "My Applications" page for the logged-in user.
     * Returns Blade (normal) or JSON (DataTables Ajax).
     */
    public function index(Request $request)
    {
    if ($request->ajax()) {
        $query = Application::query()
            ->leftJoin('properties', 'properties.id', '=', 'applications.property_id')
            ->leftJoin('users as owners', 'owners.id', '=', 'properties.user_id')
            ->where('applications.applicant_id', auth()->id())
            ->select([
                'applications.id',
                'applications.property_id',
                'applications.applicant_id',
                'applications.start_date',
                'applications.end_date',
                'applications.status',
                'applications.created_at',
                'applications.phone',
                'applications.message',
                // aliases for sorting/searching
                'properties.title as property_title',
                'owners.name as owner_name',
                'owners.email as owner_email',
            ]);
            // IMPORTANT: no ->latest(); let DataTables control ordering

        if ($status = $request->get('status')) {
            $query->where('applications.status', $status);
        }

        return DataTables::eloquent($query)
            // Display cols (you can keep relation-based access, but alias is faster)
            ->addColumn('property', fn ($row) => e($row->property_title ?? '—'))
            ->addColumn('owner', fn ($row) => e($row->owner_name ?? '—'))
            ->addColumn('dates', function ($row) {
                $start = $row->start_date ? Carbon::parse($row->start_date)->format('d M Y') : '—';
                $end   = $row->end_date   ? Carbon::parse($row->end_date)->format('d M Y')   : '—';
                return '<span class="text-nowrap">'.e($start).' → '.e($end).'</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $status = strtolower($row->status ?? 'pending');
                $map = [
                    'accepted' => ['class' => 'accepted', 'label' => 'accepted'],
                    'rejected' => ['class' => 'rejected', 'label' => 'rejected'],
                    'pending'  => ['class' => 'pending',  'label' => 'pending'],
                ];
                $chip = $map[$status] ?? $map['pending'];
                return '<span class="chip '.$chip['class'].' text-capitalize"><span class="dot"></span>'.e($chip['label']).'</span>';
            })
            ->addColumn('actions', function ($row) {
                $csrf      = csrf_token();
                $showUrl   = route('owner.applications.show', $row->id);
                $canCancel = ($row->status === 'pending');
                $cancelBtn = '';

                if ($canCancel) {
                    $cancelBtn = '
                      <form method="POST" class="d-inline js-confirm js-cancel"
                            data-title="Cancel this application?"
                            data-text="This will permanently remove your pending application."
                            action="'.route('owner.applications.destroy', $row->id).'">
                        <input type="hidden" name="_token" value="'.$csrf.'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn-icon reject" type="submit" title="Cancel">
                          <i class="bi bi-x"></i>
                        </button>
                      </form>';
                }

                return '
                  <div class="d-inline-flex gap-2">
                    <button type="button" class="btn-icon js-view" title="View details"
                            data-url="'.$showUrl.'">
                      <i class="bi bi-eye"></i>
                    </button>
                    '.$cancelBtn.'
                  </div>';
            })

            // Map the rendered columns to real DB fields for ordering:
            ->orderColumn('property', fn ($q, $order) => $q->orderBy('property_title', $order))
            ->orderColumn('owner',    fn ($q, $order) => $q->orderBy('owner_name', $order))
            ->orderColumn('dates',    fn ($q, $order) => $q->orderBy('applications.start_date', $order))
            ->orderColumn('status_badge', fn ($q, $order) => $q->orderBy('applications.status', $order))

            // Optional: global search should hit property, owner, email, status
            ->filter(function ($q) use ($request) {
                $search = $request->input('search.value');
                if (!$search) return;
                $like = '%'.$search.'%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('property_title', 'like', $like)
                        ->orWhere('owner_name', 'like', $like)
                        ->orWhere('owner_email', 'like', $like)
                        ->orWhere('applications.status', 'like', $like);
                });
            })

            ->rawColumns(['dates','status_badge','actions'])
            ->toJson();
    }

        // Shell view — rows come via DataTables Ajax
        return view('owner.applications.index');
    }

    /**
     * Show an application (for the modal).
     */
    public function show(Application $application)
    {
        // Only the applicant can view their own application details here
        abort_unless($application->applicant_id === auth()->id(), 403);

        $application->loadMissing(['property:id,title,user_id', 'property.user:id,name,email']);

        $payload = [
            'id'           => $application->id,
            'status'       => $application->status,
            'submitted_at' => optional($application->created_at)->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            'start_date'   => $application->start_date ? Carbon::parse($application->start_date)->format('d M Y') : null,
            'end_date'     => $application->end_date   ? Carbon::parse($application->end_date)->format('d M Y')   : null,
            'property'     => [
                'title' => $application->property?->title,
            ],
            'owner'        => [
                'name'  => $application->property?->user?->name,
                'email' => $application->property?->user?->email,
            ],
            'extras'       => array_filter([
                'phone'   => $application->getAttribute('phone'),
                'message' => $application->getAttribute('message'),
            ], fn($v) => !is_null($v) && $v !== ''),
        ];

        return response()->json($payload);
    }

    /**
     * Store a new application (unchanged).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required','exists:properties,id'],
            'start_date'  => ['required','date','after_or_equal:today'],
            'end_date'    => ['required','date','after_or_equal:start_date'],
            'name'        => ['required','string','max:120'],
            'email'       => ['required','email','max:190'],
            'phone'       => ['required','string','max:50'],
            'message'     => ['nullable','string','max:1000'],
        ]);

        $property = Property::findOrFail($data['property_id']);

        if ($property->user_id === auth()->id()) {
            return back()->with('error', 'You cannot apply to your own property.');
        }
        if ($property->status !== 'active') {
            return back()->with('error', 'This property is not currently accepting applications.');
        }

        $dup = Application::where([
            'property_id'  => $property->id,
            'applicant_id' => auth()->id(),
        ])->whereIn('status', ['pending','accepted'])->exists();

        if ($dup) {
            return back()->with('error', 'You already have an active application for this property.');
        }

        Application::create([
            'property_id'  => $property->id,
            'applicant_id' => auth()->id(),
            'name'         => $data['name'],
            'email'        => $data['email'],
            'phone'        => $data['phone'] ?? null,
            'message'      => $data['message'] ?? null,
            'status'       => 'pending',
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
        ]);

        return back()->with('success', 'Application sent!');
    }

    /**
     * Cancel (delete) my pending application.
     * We DELETE the record to avoid inventing a new enum (like "cancelled").
     */
    public function destroy(Application $application)
    {
        abort_unless($application->applicant_id === auth()->id(), 403);

        if ($application->status !== 'pending') {
            return back()->with('error', 'Only pending applications can be cancelled.');
        }

        try {
            $application->delete();
            return back()->with('success', 'Application cancelled.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Could not cancel the application.');
        }
    }
}
