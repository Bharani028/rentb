{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .metric-card{border:1px solid rgba(0,0,0,.06);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    .metric-value{font-size:1.75rem;font-weight:700}
    .subtle{color:#6b7280}
    .table-clean thead th{border:0;color:#6b7280;font-weight:600}
    .table-clean tbody tr{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    .badge-soft{background:rgba(13,110,253,.08);color:#0d6efd}
    .section-title{font-weight:700}
  </style>
@endpush

@section('content')
<div class="container-fluid py-4">

  {{-- Page header --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h3 class="mb-0">Admin Dashboard</h3>
      <div class="subtle">Quick overview of platform activity</div>
    </div>
    <div>
      <a href="{{ route('admin.properties.index') }}" class="btn btn-primary">
        <i class="bi bi-check2-square me-1"></i> Review Properties
      </a>
    </div>
  </div>

  {{-- Metrics row --}}
  <div class="row g-3">
    @php
      $m = $metrics ?? [
        'total_users' => $totalUsers ?? 0,
        'active_properties' => $activeProperties ?? 0,
        'pending_properties' => $pendingPropertiesCount ?? 0,
        'bookings_today' => $bookingsToday ?? 0,
      ];
    @endphp

    <div class="col-md-3">
      <div class="card metric-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="subtle">Total Users</div>
            <div class="metric-value">{{ number_format($m['total_users']) }}</div>
          </div>
          <i class="bi bi-people fs-2 text-primary"></i>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card metric-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="subtle">Active Properties</div>
            <div class="metric-value">{{ number_format($m['active_properties']) }}</div>
          </div>
          <i class="bi bi-houses fs-2 text-success"></i>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card metric-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="subtle">Pending Approvals</div>
            <div class="metric-value">{{ number_format($m['pending_properties']) }}</div>
          </div>
          <i class="bi bi-hourglass-split fs-2 text-warning"></i>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card metric-card p-3 h-100">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="subtle">Bookings Today</div>
            <div class="metric-value">{{ number_format($m['bookings_today']) }}</div>
          </div>
          <i class="bi bi-calendar-check fs-2 text-info"></i>
        </div>
      </div>
    </div>
  </div>

  {{-- Lists --}}
  <div class="row g-4 mt-1">
    <div class="col-lg-6">
      <div class="card metric-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="section-title">Pending Properties</div>
            <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="badge badge-soft text-decoration-none">
              View all
            </a>
          </div>

          @php $pending = $pendingProperties ?? []; @endphp

          <div class="table-responsive">
            <table class="table table-clean align-middle">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Host</th>
                  <th>Submitted</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pending as $p)
                  <tr>
                    <td class="fw-semibold">
                      <a href="{{ route('admin.properties.show', $p->id) }}" class="text-decoration-none">
                        {{ $p->title }}
                      </a>
                    </td>
                    <td>{{ $p->user->name ?? '—' }}</td>
                    <td>{{ $p->created_at?->format('d M Y') }}</td>
                    <td class="text-end">
                      <a href="{{ route('admin.properties.show', $p->id) }}" class="btn btn-sm btn-outline-primary">
                        Review
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center subtle py-4">No pending properties 🎉</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card metric-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="section-title">Recent Bookings</div>
            <a href="{{ route('admin.bookings.index') }}" class="badge badge-soft text-decoration-none">
              View all
            </a>
          </div>

          @php $recent = $recentBookings ?? []; @endphp

          <div class="table-responsive">
            <table class="table table-clean align-middle">
              <thead>
                <tr>
                  <th>Guest</th>
                  <th>Property</th>
                  <th>Dates</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recent as $b)
                  <tr>
                    <td>{{ $b->user->name ?? '—' }}</td>
                    <td>
                      <a href="{{ route('admin.properties.show', $b->property_id) }}" class="text-decoration-none">
                        {{ $b->property->title ?? 'Property #'.$b->property_id }}
                      </a>
                    </td>
                    <td>{{ \Illuminate\Support\Str::of($b->start_date)->toString() }} → {{ \Illuminate\Support\Str::of($b->end_date)->toString() }}</td>
                    <td>
                      <span class="badge text-bg-{{ $b->status === 'confirmed' ? 'success' : ($b->status === 'pending' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($b->status) }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center subtle py-4">No bookings yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
