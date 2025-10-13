@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Booking Details</h3>
      <div class="subtle">Detailed view of the booking request</div>
    </div>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Back to Bookings</a>
  </div>

  <div class="card modern-card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <h5 class="mb-3">Guest Information</h5>
          <p><strong>Name:</strong> {{ $booking->applicant->name ?? '—' }}</p>
          <p><strong>Email:</strong> {{ $booking->applicant->email ?? '—' }}</p>
          <p><strong>Phone:</strong> {{ $booking->phone ?? '—' }}</p>
        </div>
        <div class="col-md-6">
          <h5 class="mb-3">Property Information</h5>
          <p><strong>Title:</strong> <a href="{{ route('properties.show', $booking->property_id) }}" target="_blank">{{ $booking->property->title ?? '—' }}</a></p>
          <p><strong>Host:</strong> {{ $booking->property->user->name ?? '—' }}</p>
        </div>
      </div>

      <h5 class="mt-4">Booking Dates</h5>
      <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') ?? '—' }}</p>
      <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') ?? '—' }}</p>

      <h5 class="mt-4">Message</h5>
      <p>{{ $booking->message ?? '—' }}</p>

      <h5 class="mt-4">Status</h5>
      <span class="status-badge {{ 'status-' . strtolower($booking->status) }}">{{ ucfirst($booking->status) }}</span>
    </div>
  </div>
</div>
@endsection
