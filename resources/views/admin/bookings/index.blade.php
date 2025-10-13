@extends('layouts.admin')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .card.modern-card{border:1px solid rgba(0,0,0,.06);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    #bookingsTable{border-collapse:separate!important;border-spacing:0 8px}
    #bookingsTable thead th{border:0;color:#6b7280;font-weight:600}
    #bookingsTable tbody tr{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    #bookingsTable tbody td{border-top:0;vertical-align:middle}
    .subtle{color:#6b7280}
    .status-badge{padding:.35rem .55rem;border-radius:999px;font-weight:600}
    .status-pending{background:rgba(234,179,8,.15);color:#a16207}
    .status-confirmed{background:rgba(16,185,129,.15);color:#047857}
    .status-cancelled{background:rgba(107,114,128,.15);color:#374151}
  </style>
@endpush

@section('content')
<div class="container-fluid py-4">
  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Bookings</h3>
      <div class="subtle">Global view of all reservations</div>
    </div>
  </div>

  {{-- Filters (sent as ajax params to DT) --}}
  <div class="card modern-card mb-3">
    <div class="card-body py-3">
      <form id="filters" class="row g-2">
        <div class="col-md-3">
          <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search guest, host, property…">
        </div>
        <div class="col-md-2">
          <select class="form-select" name="status">
            {{-- values are internal DB statuses --}}
            <option value="">Any status</option>
            <option value="pending"  @selected(request('status')==='pending')>Pending</option>
            <option value="accepted" @selected(request('status')==='accepted')>Confirmed</option>
            <option value="rejected" @selected(request('status')==='rejected')>Cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" class="form-control" name="start" value="{{ request('start') }}">
        </div>
        <div class="col-md-2">
          <input type="date" class="form-control" name="end" value="{{ request('end') }}">
        </div>
        <div class="col-md-2">
          <select class="form-select" name="date_type">
            <option value="stay"    @selected(request('date_type')==='stay')>Stay dates</option>
            <option value="created" @selected(request('date_type','created')==='created')>Created date</option>
          </select>
        </div>
        <div class="col-md-1 d-grid">
          <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card modern-card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="bookingsTable" class="table align-middle table-hover nowrap" style="width:100%">
          <thead>
            <tr>
              <th>Guest</th>
              <th>Host</th>
              <th>Property</th>
              <th>Dates</th>
              <th>Created</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- View Booking Modal --}}
  <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewBookingModalLabel">Booking Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="viewBookingContent"></div>
        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      $.fn.dataTable.ext.errMode = 'console';

      const $filters = $('#filters');

      // DataTables (server-side like Users page)
      const table = $('#bookingsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false, // we use our custom filter bar
        pageLength: 10,
        lengthMenu: [[10,25,50,100],[10,25,50,100]],
        order: [[4, 'desc']],
        ajax: {
          url: "{{ route('admin.bookings.index') }}",
          data: function (d) {
            const data = Object.fromEntries(new FormData($filters[0]).entries());
            return Object.assign(d, data);
          }
        },
        columns: [
          { data: 'guest',    name: 'applicant.name' },
          { data: 'host',     name: 'property.user.name', defaultContent: '—' },
          { data: 'property', name: 'property.title', orderable: false, searchable: false },
          { data: 'dates',    name: 'start_date', searchable: false },
          { data: 'created',  name: 'created_at' },
          { data: 'status',   name: 'status', orderable: false, searchable: false },
          { data: 'actions',  orderable: false, searchable: false, className: 'text-end' },
        ],
        language: {
          emptyTable: 'No bookings found.',
          lengthMenu: 'Show _MENU_ entries',
          info: 'Showing _START_ to _END_ of _TOTAL_ entries',
          infoEmpty: 'Showing 0 to 0 of 0 entries'
        },
        dom: '<"row align-items-center mb-2"' +
               '<"col-md-6"l>' +
               '<"col-md-6 text-end"f>' +
             '>' +
             't' +
             '<"row align-items-center mt-2"' +
               '<"col-md-6"i>' +
               '<"col-md-6"p>' +
             '>',
      });

      // re-load table when filters submit
      $filters.on('submit', function (e) {
        e.preventDefault();
        table.ajax.reload();
      });

      // Toasts (top-right, 5s, pause on hover)
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (t) => {
          t.addEventListener('mouseenter', Swal.stopTimer);
          t.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });

      @if (session('success')) Toast.fire({ icon: 'success', title: @json(session('success')) }); @endif
      @if (session('info'))    Toast.fire({ icon: 'info',    title: @json(session('info')) }); @endif
      @if (session('warning')) Toast.fire({ icon: 'warning', title: @json(session('warning')) }); @endif
      @if ($errors->any())     Toast.fire({ icon: 'error',   title: 'There were some problems.' }); @endif

      // POST helper (CSRF)
      function postAction(url, fields = {}) {
        const form = document.createElement('form');
        form.method = 'POST'; form.action = url;
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrf);
        for (const [name, value] of Object.entries(fields)) {
          const input = document.createElement('input');
          input.type = 'hidden'; input.name = name; input.value = value ?? '';
          form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
      }

      // Eye icon -> popup like users application page
      $('#bookingsTable').on('click', '.js-view', function () {
        const id = $(this).data('id');
        $.get("{{ route('admin.bookings.index') }}/" + id, { ajax: 1 })
          .done(function (data) {
            const badgeMap = { pending:'status-pending', accepted:'status-confirmed', rejected:'status-cancelled' };
            const badge = `<span class="status-badge ${badgeMap[data.status] || 'status-pending'}">${(data.status==='accepted'?'Confirmed':(data.status==='rejected'?'Cancelled':'Pending'))}</span>`;

            const html = `
              <div class="text-start">
                <div class="mb-2"><strong>Guest:</strong> ${_.escape(data.guest || '—')}</div>
                <div class="mb-2"><strong>Host:</strong> ${_.escape(data.host || '—')}</div>
                <div class="mb-2"><strong>Property:</strong> <a href="${data.property.url}" target="_blank" rel="noopener">${_.escape(data.property.title)}</a></div>
                <div class="mb-2"><strong>Dates:</strong> ${_.escape(data.dates.start)} → ${_.escape(data.dates.end)}</div>
                <div class="mb-2"><strong>Created:</strong> ${_.escape(data.created_at || '')}</div>
                <div class="mb-2"><strong>Status:</strong> ${badge}</div>
                ${data.message ? `<div class="mt-3"><strong>Message from guest</strong><div class="border rounded p-2 mt-1" style="white-space:pre-wrap">${_.escape(data.message)}</div></div>` : ''}
              </div>
            `;

            $('#viewBookingContent').html(html);
            $('#viewBookingModal').modal('show');
          })
          .fail(() => Toast.fire({ icon: 'error', title: 'Failed to load details.' }));
      });

      // Reject from table list
      $('#bookingsTable').on('click', '.js-reject', function () {
        const id = $(this).data('id');
        const title = $(this).data('title') || 'this application';
        Swal.fire({
          icon: 'warning',
          title: 'Reject application?',
          html: `<div class="text-muted mb-2">Provide an optional reason for rejecting <b>${title}</b>.</div>`,
          input: 'textarea',
          inputPlaceholder: 'Reason (optional)…',
          showCancelButton: true,
          confirmButtonText: 'Reject',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#dc3545'
        }).then(res => {
          if (res.isConfirmed) {
            postAction("{{ url('/admin/bookings') }}/" + id + "/cancel", { reason: res.value || 'Rejected by admin' });
          }
        });
      });
    });
  </script>

  {{-- tiny lodash escape helper (for safe HTML in popup) --}}
  <script>
    window._ = window._ || {
      escape: function (s) {
        if (s == null) return '';
        return String(s)
          .replace(/&/g,'&amp;').replace(/</g,'&lt;')
          .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
      }
    };
  </script>
@endpush
