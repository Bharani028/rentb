@extends('layouts.admin')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .card.modern-card{border:1px solid rgba(0,0,0,.06);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    #usersTable{border-collapse:separate!important;border-spacing:0 8px}
    #usersTable thead th{border:0;color:#6b7280;font-weight:600}
    #usersTable tbody tr{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    #usersTable tbody td{border-top:0;vertical-align:middle}
    .subtle{color:#6b7280}
    .role-badge{background:rgba(99,102,241,.12);color:#6366f1}
    .status-active{background:rgba(16,185,129,.12);color:#10b981}
    .status-inactive{background:rgba(239,68,68,.12);color:#ef4444}
  </style>
@endpush

@section('content')
<div class="container-fluid py-4">
  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Users</h3>
      <div class="subtle">Manage members, roles, and access</div>
    </div>
    <div>
      <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> New User
      </a>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card modern-card mb-3">
    <div class="card-body py-3">
      <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2" id="filter-form">
        <div class="col-md-4">
          <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name or email…">
        </div>
        <div class="col-md-3">
          <select class="form-select" name="role">
            <option value="">All roles</option>
            @foreach($roles as $r)
              <option value="{{ $r }}" @selected(request('role')===$r)>{{ ucfirst($r) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select" name="status">
            <option value="">Any status</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-outline-secondary"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Users Table --}}
  <div class="card modern-card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="usersTable" class="table align-middle table-hover nowrap" style="width:100%">
          <thead>
            <tr>
              <th>User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal for User Details -->
  <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="userModalLabel">User Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8">
              <h4 id="modal-user-name"></h4>
              <p><strong>Email:</strong> <span id="modal-user-email"></span></p>
              <p><strong>Role:</strong> <span id="modal-user-role"></span></p>
              <p><strong>Joined:</strong> <span id="modal-user-joined"></span></p>
              <p><strong>Status:</strong> <span id="modal-user-status"></span></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
          url: '{{ route('admin.users.index') }}',
          data: function (d) {
            d.q = $('input[name="q"]').val();
            d.role = $('select[name="role"]').val();
            d.status = $('select[name="status"]').val();
          }
        },
        columns: [
          { data: 'user_link', name: 'name', orderable: true, searchable: true }, // Column 0
          { data: 'email', name: 'email', orderable: true, searchable: true },    // Column 1
          { data: 'role_badge', name: 'roles.name', orderable: false, searchable: false }, // Column 2
          { data: 'joined', name: 'created_at', orderable: true, searchable: false }, // Column 3
          { data: 'status_badge', name: 'status', orderable: true, searchable: true }, // Column 4
          { data: 'actions', name: 'actions', orderable: false, searchable: false } // Column 5
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[3, 'desc']], // Default order by Joined (created_at)
        language: {
          emptyTable: 'No users found.',
          lengthMenu: 'Show _MENU_ entries',
          info: 'Showing _START_ to _END_ of _TOTAL_ entries',
          infoEmpty: 'Showing 0 to 0 of 0 entries'
        },
        dom: '<"row align-items-center mb-2"<"col-md-6"l><"col-md-6">>' +
             't' +
             '<"row align-items-center mt-2"<"col-md-6"i><"col-md-6"p>>'
      });

      // Reload table on filter form submission
      $('#filter-form').on('submit', function (e) {
        e.preventDefault();
        table.draw();
      });

      // Toasts for Laravel flash messages
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

      @if (session('success')) Toast.fire({ icon: 'success', title: @json(session('success')) }); @endif
      @if (session('warning')) Toast.fire({ icon: 'warning', title: @json(session('warning')) }); @endif
      @if ($errors->any()) Toast.fire({ icon: 'error', title: 'There were some problems.' }); @endif

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

      // Suspend confirm
      $(document).on('click', '.js-suspend', function (e) {
        e.preventDefault();
        const url = $(this).closest('form').attr('action');
        const title = $(this).data('title') || 'this user';
        Swal.fire({
          icon: 'warning',
          title: 'Suspend user?',
          html: `<div class="text-muted">You are suspending <b>${title}</b>.</div>`,
          showCancelButton: true,
          confirmButtonText: 'Suspend',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#dc3545'
        }).then(res => {
          if (res.isConfirmed) {
            postAction(url);
          }
        });
      });

      // Restore confirm
      $(document).on('click', '.js-restore', function (e) {
        e.preventDefault();
        const url = $(this).closest('form').attr('action');
        const title = $(this).data('title') || 'this user';
        Swal.fire({
          icon: 'question',
          title: 'Restore user?',
          html: `<div class="text-muted">You are restoring <b>${title}</b>.</div>`,
          showCancelButton: true,
          confirmButtonText: 'Restore',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#16a34a'
        }).then(res => {
          if (res.isConfirmed) {
            postAction(url);
          }
        });
      });

      // View user details in modal
      $(document).on('click', '.view-user', function (e) {
        e.preventDefault();
        const userId = $(this).data('id');
        $.ajax({
          url: '{{ route('admin.users.show', ['user' => '__id__']) }}'.replace('__id__', userId),
          method: 'GET',
          success: function (response) {
            $('#modal-user-name').text(response.name || 'User #' + userId);
            $('#modal-user-email').text(response.email);
            $('#modal-user-role').text(response.roles.length ? response.roles[0].name : 'guest');
            $('#modal-user-joined').text(response.created_at ? new Date(response.created_at).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }) : '—');
            $('#modal-user-status').text(response.status);
            $('#userModal').modal('show');
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Unable to load user details.',
            });
          }
        });
      });
    });
  </script>
@endpush