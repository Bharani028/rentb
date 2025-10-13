{{-- resources/views/admin/properties/index.blade.php --}}
@extends('layouts.admin')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .card.modern-card{border:1px solid rgba(0,0,0,.06);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    #propsTable{border-collapse:separate!important;border-spacing:0 8px}
    #propsTable thead th{border:0;color:#6b7280;font-weight:600}
    #propsTable tbody tr{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    #propsTable tbody td{border-top:0;vertical-align:middle}
    .subtle{color:#6b7280}
    .status-badge{padding:.35rem .55rem;border-radius:999px;font-weight:600}
    .status-pending{background:rgba(234,179,8,.15);color:#a16207}
    .status-active{background:rgba(16,185,129,.15);color:#047857}
    .status-rejected{background:rgba(239,68,68,.15);color:#b91c1c}
    .thumb{width:56px;height:40px;object-fit:cover;border-radius:8px;border:1px solid rgba(0,0,0,.06)}
  </style>
@endpush

@section('content')
<div class="container-fluid py-4">
  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Properties</h3>
      <div class="subtle">Moderate and manage all listings</div>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
        <i class="bi bi-hourglass-split me-1"></i> Pending
      </a>
      <a href="{{ route('admin.properties.index', ['status' => 'active']) }}" class="btn btn-outline-success">
        <i class="bi bi-check2-circle me-1"></i> Active
      </a>
      <a href="{{ route('admin.properties.index', ['status' => 'rejected']) }}" class="btn btn-outline-danger">
        <i class="bi bi-x-octagon me-1"></i> Rejected
      </a>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card modern-card mb-3">
    <div class="card-body py-3">
      <form method="GET" action="{{ route('admin.properties.index') }}" class="row g-2" id="filter-form">
        <div class="col-md-4">
          <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search title, host, city…">
        </div>
        <div class="col-md-3">
          <select class="form-select" name="status">
            @php $statuses = ['' => 'Any status', 'pending' => 'Pending', 'active' => 'Active', 'rejected' => 'Rejected']; @endphp
            @foreach($statuses as $val => $label)
              <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" name="city">
            <option value="">Any city</option>
            @foreach(($cities ?? []) as $c)
              <option value="{{ $c }}" @selected(request('city')===$c)>{{ $c }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-outline-secondary"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card modern-card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="propsTable" class="table align-middle table-hover nowrap" style="width:100%">
          <thead>
            <tr>
              <th></th>
              <th>Title</th>
              <th>Host</th>
              <th>City</th>
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
</div>
@endsection

@push('scripts')
  {{-- libs --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // DataTables: server-side processing
      const table = $('#propsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: '{{ route('admin.properties.index') }}',
          data: function (d) {
            d.q = $('input[name="q"]').val();
            d.status = $('select[name="status"]').val();
            d.city = $('select[name="city"]').val();
          }
        },
        columns: [
          { data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false },
          { data: 'title_link', name: 'title' },
          { data: 'user_name', name: 'user.name' },
          { data: 'city_name', name: 'city' },
          { data: 'created', name: 'created_at' },
          { data: 'status_badge', name: 'status' },
          { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[4, 'desc']],
        language: {
          emptyTable: 'No properties found.',
          lengthMenu: 'Show _MENU_ entries',
          search: 'Search:',
          info: 'Showing _START_ to _END_ of _TOTAL_ entries',
          infoEmpty: 'Showing 0 to 0 of 0 entries'
        },
        dom: '<"row align-items-center mb-2"<"col-md-6"l><"col-md-6 text-end"f>>' +
             't' +
             '<"row align-items-center mt-2"<"col-md-6"i><"col-md-6"p>>'
      });

      // Reload table on filter form submission
      $('#filter-form').on('submit', function (e) {
        e.preventDefault();
        table.draw();
      });

      // Toasts to match users page: top-right, 5s, pause on hover
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });

      // Convert Laravel flashes -> toasts
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

      // Approve confirm
      $(document).on('click', '.js-approve', function () {
        const url = $(this).data('action');
        const title = $(this).data('title') || 'this property';
        Swal.fire({
          icon: 'question',
          title: 'Approve listing?',
          html: `<div class="text-muted">You are approving <b>${title}</b>. It will be publicly visible.</div>`,
          showCancelButton: true,
          confirmButtonText: 'Approve',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#16a34a'
        }).then(res => {
          if (res.isConfirmed) {
            postAction(url);
          }
        });
      });

      // Reject prompt
      $(document).on('click', '.js-reject', function () {
        const url = $(this).data('action');
        const title = $(this).data('title') || 'this property';
        Swal.fire({
          icon: 'warning',
          title: 'Reject listing?',
          html: `<div class="text-muted mb-2">Provide an optional reason for rejecting <b>${title}</b>.</div>`,
          input: 'textarea',
          inputPlaceholder: 'Explain why this listing is rejected…',
          showCancelButton: true,
          confirmButtonText: 'Reject',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#dc3545',
          preConfirm: (reason) => reason
        }).then(res => {
          if (res.isConfirmed) {
            postAction(url, { reason: res.value || 'Manually rejected by admin' });
          }
        });
      });
    });
  </script>
@endpush