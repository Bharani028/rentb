<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BookingsController extends Controller
{
    public function index(Request $request)
    {
        // When DataTables hits this route via AJAX, serve JSON
        if ($request->ajax()) {
            $q        = $request->string('q')->toString();
            $status   = $request->string('status')->toString();            // pending|accepted|rejected
            $dateType = $request->string('date_type', 'created')->toString(); // created|stay
            $start    = $request->date('start');
            $end      = $request->date('end');

            $query = Application::query()
                ->with(['applicant', 'property.user'])
                ->when($q, function (Builder $qb) use ($q) {
                    $qb->where(function (Builder $w) use ($q) {
                        $w->whereHas('applicant', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                          ->orWhereHas('property.user', fn ($h) => $h->where('name', 'like', "%{$q}%"))
                          ->orWhereHas('property', fn ($p) => $p->where('title', 'like', "%{$q}%"));
                    });
                })
                ->when($status, fn (Builder $qb) => $qb->where('status', $status))
                ->when($start || $end, function (Builder $qb) use ($start, $end, $dateType) {
                    $from = $start ? Carbon::parse($start)->startOfDay() : null;
                    $to   = $end   ? Carbon::parse($end)->endOfDay()   : null;

                    if ($dateType === 'stay') {
                        $qb->where(function (Builder $w) use ($from, $to) {
                            if ($from && $to) {
                                $w->where('start_date', '<=', $to)
                                  ->where('end_date', '>=', $from);
                            } elseif ($from) {
                                $w->where('end_date', '>=', $from);
                            } elseif ($to) {
                                $w->where('start_date', '<=', $to);
                            }
                        });
                    } else {
                        if ($from && $to) {
                            $qb->whereBetween('created_at', [$from, $to]);
                        } elseif ($from) {
                            $qb->where('created_at', '>=', $from);
                        } elseif ($to) {
                            $qb->where('created_at', '<=', $to);
                        }
                    }
                })
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('guest', fn ($app) => e($app->applicant->name ?? '—'))
                ->addColumn('host', fn ($app) => e(optional($app->property->user)->name ?? '—'))
                ->addColumn('property', function ($app) {
                    $title = e($app->property->title ?? ('Property #'.$app->property_id));
                    $url   = route('properties.show', $app->property_id);
                    return '<a href="'.$url.'" target="_blank" rel="noopener" class="text-decoration-none">'.$title.'</a>';
                })
                ->addColumn('dates', function ($app) {
                    $s = $app->start_date ? Carbon::parse($app->start_date)->format('d M Y') : (string) $app->start_date;
                    $e = $app->end_date   ? Carbon::parse($app->end_date)->format('d M Y')   : (string) $app->end_date;
                    return e($s.' → '.$e);
                })
                ->addColumn('created', fn ($app) => optional($app->created_at)->format('d M Y'))
                ->addColumn('status', function ($app) {
                    // internal -> UI mapping
                    $internal = $app->status ?? 'pending'; // pending|accepted|rejected
                    $ui = $internal === 'accepted' ? 'confirmed' : ($internal === 'rejected' ? 'cancelled' : 'pending');
                    $cls = $ui === 'confirmed' ? 'status-confirmed' : ($ui === 'cancelled' ? 'status-cancelled' : 'status-pending');
                    return '<span class="status-badge '.$cls.'">'.ucfirst($ui).'</span>';
                })
                ->addColumn('actions', function ($app) {
                    $viewBtn = '<button class="btn btn-sm btn-outline-secondary js-view" data-id="'.$app->id.'" title="View"><i class="bi bi-eye"></i></button>';
                    return '<div class="btn-group">'.$viewBtn.'</div>';
                })
                ->rawColumns(['property','status','actions'])
                ->make(true);
        }

        // Non-AJAX: render page shell; rows come from server-side JSON
        return view('admin.bookings.index');
    }

    public function show(Request $request, $id)
    {
        $app = Application::with(['applicant', 'property.user'])->findOrFail($id);

        // For the popup we return compact JSON so the front-end can render HTML nicely
        if ($request->ajax()) {
            return response()->json([
                'id'         => $app->id,
                'guest'      => $app->applicant->name ?? '—',
                'host'       => optional($app->property->user)->name ?? '—',
                'property'   => [
                    'title' => $app->property->title ?? ('Property #'.$app->property_id),
                    'url'   => route('properties.show', $app->property_id),
                ],
                'dates'      => [
                    'start' => $app->start_date ? Carbon::parse($app->start_date)->format('d M Y') : (string) $app->start_date,
                    'end'   => $app->end_date   ? Carbon::parse($app->end_date)->format('d M Y')   : (string) $app->end_date,
                ],
                'status'     => $app->status, // internal (pending|accepted|rejected)
                'created_at' => optional($app->created_at)->format('d M Y, H:i'),
                'message'    => $app->message ?? null,
            ]);
        }

        // (Optional) fallback blade if navigated directly
        $app->setRelation('user', $app->applicant);
        return view('admin.bookings.show', ['booking' => $app]);
    }

    public function cancel(Request $request, $id)
    {
        // Admin cannot cancel an application here. Keeping this method for consistency.
        return back()->with('info', 'Applications cannot be cancelled directly from here.');
    }

    public function destroy($id)
    {
        Application::findOrFail($id)->delete();
        return redirect()->route('admin.bookings.index')->with('success', 'Application deleted.');
    }
}
