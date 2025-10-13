<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Join to expose sortable/searchable columns for applicant & property
            $query = Application::query()
                ->leftJoin('users as applicants', 'applicants.id', '=', 'applications.applicant_id')
                ->leftJoin('properties', 'properties.id', '=', 'applications.property_id')
                ->where('properties.user_id', auth()->id()) // only apps to my listings
                ->select([
                    'applications.id',
                    'applications.applicant_id',
                    'applications.property_id',
                    'applications.start_date',
                    'applications.end_date',
                    'applications.status',
                    'applications.created_at',
                    'applicants.name  as applicant_name',
                    'applicants.email as applicant_email',
                    'properties.title as property_title',
                ]);
                // IMPORTANT: no ->latest(); let DataTables control ordering

            if ($status = $request->get('status')) {
                $query->where('applications.status', $status);
            }

            return DataTables::eloquent($query)
                // Display columns
                ->addColumn('applicant', function ($row) {
                    return e($row->applicant_name ?? '—');
                })
                ->addColumn('property', function ($row) {
                    return e($row->property_title ?? '—');
                })
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
                    $approveDisabled = $row->status === 'accepted' ? 'disabled' : '';
                    $rejectDisabled  = $row->status === 'rejected' ? 'disabled' : '';
                    $approveAction   = route('owner.bookings.approve', $row->id);
                    $rejectAction    = route('owner.bookings.reject',  $row->id);
                    $showUrl         = route('owner.bookings.show',    $row->id);
                    $csrf            = csrf_token();

                    return '
                    <div class="d-inline-flex gap-2">
                      <button type="button" class="btn-icon js-view" title="View details" data-url="'.$showUrl.'">
                        <i class="bi bi-eye"></i>
                      </button>

                      <form method="POST" class="js-confirm js-approve d-inline"
                            data-title="Accept this application?"
                            data-text="The applicant will be marked as accepted."
                            action="'.$approveAction.'">
                        <input type="hidden" name="_token" value="'.$csrf.'">
                        <input type="hidden" name="_method" value="PATCH">
                        <button class="btn-icon accept" type="submit" title="Accept" '.$approveDisabled.'>
                          <i class="bi bi-check2"></i>
                        </button>
                      </form>

                      <form method="POST" class="js-confirm js-reject d-inline"
                            data-title="Reject this application?"
                            data-text="You can accept again later if needed."
                            action="'.$rejectAction.'">
                        <input type="hidden" name="_token" value="'.$csrf.'">
                        <input type="hidden" name="_method" value="PATCH">
                        <button class="btn-icon reject" type="submit" title="Reject" '.$rejectDisabled.'>
                          <i class="bi bi-x"></i>
                        </button>
                      </form>
                    </div>';
                })

                // Map rendered columns -> real DB fields for ordering
                ->orderColumn('applicant', function ($q, $order) {
                    $q->orderBy('applicant_name', $order);
                })
                ->orderColumn('property', function ($q, $order) {
                    $q->orderBy('property_title', $order);
                })
                ->orderColumn('dates', function ($q, $order) {
                    $q->orderBy('applications.start_date', $order);
                })
                ->orderColumn('status_badge', function ($q, $order) {
                    $q->orderBy('applications.status', $order);
                })
                ->filter(function ($q) use ($request) {
        $search = $request->input('search.value'); // DataTables global search text
        if (!$search) return;

        $q->where(function ($sub) use ($search) {
            $like = '%'.$search.'%';
            $sub->where('applicants.name', 'like', $like)
                ->orWhere('applicants.email', 'like', $like)
                ->orWhere('properties.title', 'like', $like)
                ->orWhere('applications.status', 'like', $like);
        });
    })
                ->rawColumns(['dates','status_badge','actions'])
                ->toJson();
        }

        return view('owner.bookings.index');
    }

    public function show(Application $application)
    {
        // Keep using the model for the modal details
        $this->authorizeForPropertyOwner($application);
        $application->loadMissing(['applicant:id,name,email', 'property:id,title,user_id']);

        $payload = [
            'id'           => $application->id,
            'status'       => $application->status,
            'submitted_at' => optional($application->created_at)->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            'start_date'   => $application->start_date ? Carbon::parse($application->start_date)->format('d M Y') : null,
            'end_date'     => $application->end_date   ? Carbon::parse($application->end_date)->format('d M Y')   : null,
            'applicant'    => [
                'name'  => $application->applicant?->name,
                'email' => $application->applicant?->email,
            ],
            'property'     => [
                'title' => $application->property?->title,
            ],
            'extras'       => array_filter([
                'phone'    => $application->getAttribute('phone'),
                'guests'   => $application->getAttribute('guests'),
                'adults'   => $application->getAttribute('adults'),
                'children' => $application->getAttribute('children'),
                'budget'   => $application->getAttribute('budget'),
                'message'  => $application->getAttribute('message'),
                'notes'    => $application->getAttribute('notes'),
            ], fn($v) => !is_null($v) && $v !== ''),
        ];

        return response()->json($payload);
    }

    public function approve(Application $application)
    {
        try {
            $this->authorizeForPropertyOwner($application);
            $application->update(['status' => 'accepted']);
            return back()->with('success', 'Application accepted.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Could not accept the application.');
        }
    }

    public function reject(Application $application)
    {
        try {
            $this->authorizeForPropertyOwner($application);
            $application->update(['status' => 'rejected']);
            return back()->with('success', 'Application rejected.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Could not reject the application.');
        }
    }

    protected function authorizeForPropertyOwner(Application $application)
    {
        $application->loadMissing('property');
        abort_unless($application->property && $application->property->user_id === auth()->id(), 403);
    }
}
