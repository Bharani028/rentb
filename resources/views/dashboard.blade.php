@extends('layouts.owner')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .card.modern-card { border: 1px solid rgba(0,0,0,.06); border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.06); }
        #propertiesTable { border-collapse: separate !important; border-spacing: 0 8px; }
        #propertiesTable thead th { border: 0; color: #6b7280; font-weight: 600; cursor: pointer; }
        #propertiesTable tbody tr { background: #fff; box-shadow: 0 1px 0 rgba(0,0,0,.04); }
        #propertiesTable tbody td { border-top: 0 !important; padding-top: 14px; padding-bottom: 14px; }
        #propertiesTable tbody tr:hover { background: #f9fafb; }
        .thumb { width: 48px; height: 48px; display: inline-flex; overflow: hidden; }
        .thumb img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .chip { display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; padding: 6px 12px; font-weight: 600; font-size: .85rem; }
        .chip .dot { width: 8px; height: 8px; border-radius: 50%; }
        .chip.pending { background: #fff7e6; color: #92400e; }
        .chip.pending .dot { background: #f59e0b; }
        .chip.accepted { background: #ecfdf5; color: #065f46; }
        .chip.accepted .dot { background: #10b981; }
        .chip.rejected { background: #fef2f2; color: #991b1b; }
        .chip.rejected .dot { background: #ef4444; }
        .btn-icon { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid rgba(0,0,0,.08); background: #fff; transition: all .15s ease; margin-left: 4px; }
        .btn-icon:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(0,0,0,.06); }
        .btn-icon.accept { color: #059669; border-color: rgba(5,150,105,.25); }
        .btn-icon.accept:hover { background: #ecfdf5; }
        .btn-icon.reject { color: #dc2626; border-color: rgba(220,38,38,.25); }
        .btn-icon.reject:hover { background: #fef2f2; }
        .btn-icon.edit { color: #2563eb; border-color: rgba(37,99,235,.25); }
        .btn-icon.edit:hover { background: #eef2ff; }
        .btn-icon:disabled { opacity: .4; cursor: not-allowed; box-shadow: none; transform: none; }
        .dataTables_length, .dataTables_filter { display: none; }
        .dataTables_info { color: #6b7280; }
        .dataTables_paginate .pagination { gap: 6px; }
        .page-link { border-radius: 10px !important; }
        .dropdown-menu { min-width: 150px; }
        
    </style>
@endpush

@section('owner')
    <div class="container my-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="mb-0">Your Properties</h3>
                <div class="text-muted">Manage the listings you’ve posted</div>
            </div>
            <a href="{{ route('properties.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Post Property
            </a>
        </div>

        <div class="card modern-card p-3">
            <div class="row g-2 align-items-center mb-3">
                <div class="col-12 col-md">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input id="globalSearch" type="text" class="form-control" placeholder="Search properties…">
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <select id="statusFilter" class="form-select">
                        <option value="">Status: All</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="propertiesTable" class="table align-middle w-100 nowrap">
                    <thead>
                        <tr>
                            <th style="width:64px;"></th>
                            <th>Property</th>
                            <th class="d-none d-md-table-cell">Type</th>
                            <th class="">Price</th>
                            <th class="d-none d-md-table-cell">Status</th>
                            <th class="d-none d-lg-table-cell">Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let dt;

        $(function () {
            dt = $('#propertiesTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                responsive: { details: true },
                ajax: {
                    url: "{{ route('dashboard') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                order: [[5, 'desc']], // Default sort by 'Created' (index 5) descending
                columns: [
                    { data: 'thumb', name: 'thumb', searchable: false, orderable: false },
                    { data: 'property', name: 'title', orderable: true },
                    { data: 'type', name: 'property_type_id', orderable: true },
                    { data: 'price', name: 'price', orderable: true, searchable: false },
                    { data: 'status_badge', name: 'status', orderable: true, searchable: true },
                    { data: 'created', name: 'created_at', orderable: true, searchable: false },
                    { data: 'actions', searchable: false, orderable: false, className: 'text-end' }
                ],
                columnDefs: [
                    { responsivePriority: 1, targets: 1 },
                    { responsivePriority: 2, targets: 6 },
                    { responsivePriority: 3, targets: 4 },
                ],
                dom: '<"dt-body"t><"d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3"ip>',
                language: {
                    processing: "Loading…",
                    info: "Showing _START_ to _END_ of _TOTAL_",
                    emptyTable: "You haven’t posted any properties yet. <a href=\"{{ route('properties.create') }}\">Create your first listing</a>.",
                    paginate: { previous: '‹', next: '›' }
                },
                initComplete: function () {
                    initializeDropdowns();
                }
            });

            dt.on('draw.dt', function () {
                initializeDropdowns();
            });

            function initializeDropdowns() {
                document.querySelectorAll('.dropdown [data-bs-toggle="dropdown"]').forEach(button => {
                    if (!button._dropdown) {
                        button._dropdown = new bootstrap.Dropdown(button);
                    }
                });
            }

            $('#globalSearch').on('input', function() { dt.search(this.value).draw(); });
            $('#statusFilter').on('change', function() { dt.ajax.reload(null, false); });
        });

        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.js-confirm');
            if (!form) return;
            e.preventDefault();

            const title = form.dataset.title || 'Are you sure?';
            const text = form.dataset.text || '';
            const isDelete = form.classList.contains('js-delete');
            const isActive = form.classList.contains('js-status-active');
            const isInactive = form.classList.contains('js-status-inactive');

            let confirmText = 'Yes, confirm';
            if (isDelete) confirmText = 'Yes, delete';
            else if (isActive) confirmText = 'Yes, set active';
            else if (isInactive) confirmText = 'Yes, set inactive';

            Swal.fire({
                title,
                text,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                confirmButtonColor: isDelete ? '#d33' : '#3085d6'
            }).then((res) => {
                if (res.isConfirmed) form.submit();
            });
        }, false);

        const toast = (icon, msg) => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2400,
                timerProgressBar: true,
                icon,
                title: msg
            });
        };

        @if(session('success')) toast('success', @json(session('success'))); @endif
        @if(session('error')) toast('error', @json(session('error'))); @endif
    </script>
@endpush