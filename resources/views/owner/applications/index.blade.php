{{-- resources/views/owner/applications/index.blade.php --}}
@extends('layouts.owner')

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    .card.modern-card{border:1px solid rgba(0,0,0,.06);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    #appsTable{border-collapse:separate!important;border-spacing:0 8px}
    #appsTable thead th{border:0;color:#6b7280;font-weight:600}
    #appsTable tbody tr{background:#fff;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    #appsTable tbody td{border-top:0!important;padding-top:14px;padding-bottom:14px}
    #appsTable tbody tr:hover{background:#f9fafb}
    .chip{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:6px 12px;font-weight:600;font-size:.85rem}
    .chip .dot{width:8px;height:8px;border-radius:50%}
    .chip.pending{background:#fff7e6;color:#92400e}.chip.pending .dot{background:#f59e0b}
    .chip.accepted{background:#ecfdf5;color:#065f46}.chip.accepted .dot{background:#10b981}
    .chip.rejected{background:#fef2f2;color:#991b1b}.chip.rejected .dot{background:#ef4444}
    .btn-icon{width:38px;height:38px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(0,0,0,.08);background:#fff;transition:all .15s ease}
    .btn-icon:hover{transform:translateY(-1px);box-shadow:0 6px 14px rgba(0,0,0,.06)}
    .btn-icon.reject{color:#dc2626;border-color:rgba(220,38,38,.25)}.btn-icon.reject:hover{background:#fef2f2}
    .dataTables_length,.dataTables_filter{display:none}
    .dataTables_info{color:#6b7280}
    .dataTables_paginate .pagination{gap:6px}
    .page-link{border-radius:10px!important}
  </style>
@endpush

@section('owner')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">My Applications</h3>
      <div class="text-muted">Requests you sent to other properties</div>
    </div>
  </div>

  <div class="card modern-card p-3">
    {{-- Toolbar --}}
    <div class="row g-2 align-items-center mb-3">
      <div class="col-12 col-md">
        <div class="input-group">
          <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
          <input id="globalSearch" type="text" class="form-control" placeholder="Search applications…">
        </div>
      </div>
      <div class="col-12 col-md-auto">
        <select id="statusFilter" class="form-select">
          <option value="">Status: All</option>
          <option value="pending">Pending</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table id="appsTable" class="table align-middle w-100 nowrap">
        <thead>
          <tr>
            <th>Property</th>
            <th>Owner</th>
            <th>Dates</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  {{-- View details modal --}}
  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewModalLabel">Application details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="viewModalContent"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
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
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    let dt;

    $(function () {
      dt = $('#appsTable').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        responsive: { details: true },
        ajax: {
          url: "{{ route('owner.applications.index') }}", // same URL (index handles Ajax)
          type: "GET",
          data: function(d){
            d.status = $('#statusFilter').val();
          }
        },
        order: [[2, 'desc']], // Dates column
        columns: [
  { data: 'property',     name: 'property_title',                 orderable: true },
  { data: 'owner',        name: 'owner_name',                     orderable: true },
  { data: 'dates',        name: 'applications.start_date',        orderable: true, searchable: false },
  { data: 'status_badge', name: 'applications.status',            orderable: true, searchable: true },
  { data: 'actions',      orderable: false, searchable: false, className: 'text-end' }
],
order: [[2, 'desc']], // Dates (applications.start_date)

        columnDefs: [
          { responsivePriority: 1, targets: 0 }, // Property
          { responsivePriority: 2, targets: 4 }, // Actions
          { responsivePriority: 3, targets: 3 }, // Status
        ],
        dom: '<"dt-body"t><"d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3"ip>',
        language: {
          processing: "Loading…",
          info: "Showing _START_ to _END_ of _TOTAL_",
          emptyTable: "No applications yet.",
          paginate: { previous: '‹', next: '›' }
        }
      });

      $('#globalSearch').on('input', function(){ dt.search(this.value).draw(); });
      $('#statusFilter').on('change', function(){ dt.ajax.reload(null, false); });
    });

    // View details (eye icon)
    document.addEventListener('click', async function (e) {
      const btn = e.target.closest('.js-view');
      if (!btn) return;

      const url = btn.getAttribute('data-url');
      if (!url) return;

      try {
        const modalEl = document.getElementById('viewModal');
        const modal = new bootstrap.Modal(modalEl);
        document.getElementById('viewModalContent').innerHTML =
          '<div class="text-center py-4">Loading…</div>';
        modal.show();

        const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!resp.ok) throw new Error('Failed to load details');
        const data = await resp.json();

        const esc = (s) => (s === null || s === undefined) ? '—' : String(s);

        let extrasHTML = '';
        if (data.extras && Object.keys(data.extras).length) {
          const rows = Object.entries(data.extras).map(([k,v]) =>
            `<tr><th class="text-capitalize text-muted small w-25">${esc(k)}</th><td>${esc(v)}</td></tr>`
          ).join('');
          extrasHTML = `
            <hr class="my-3">
            <div class="mb-2 fw-semibold">Additional details</div>
            <div class="table-responsive">
              <table class="table table-sm"><tbody>${rows}</tbody></table>
            </div>`;
        }

        document.getElementById('viewModalContent').innerHTML = `
          <div class="row gy-3">
            <div class="col-md-6">
              <div class="small text-muted">Property</div>
              <div class="fw-semibold">${esc(data.property?.title)}</div>
            </div>
            <div class="col-md-6">
              <div class="small text-muted">Owner</div>
              <div class="fw-semibold">${esc(data.owner?.name)} <span class="text-muted">(${esc(data.owner?.email)})</span></div>
            </div>
            <div class="col-md-4">
              <div class="small text-muted">Start date</div>
              <div>${esc(data.start_date)}</div>
            </div>
            <div class="col-md-4">
              <div class="small text-muted">End date</div>
              <div>${esc(data.end_date)}</div>
            </div>
            <div class="col-md-4">
              <div class="small text-muted">Submitted</div>
              <div>${esc(data.submitted_at)}</div>
            </div>
            <div class="col-12">
              <div class="small text-muted">Status</div>
              <div class="mt-1"><span class="chip ${esc((data.status || '').toLowerCase())}">
                <span class="dot"></span>${esc(data.status)}
              </span></div>
            </div>
          </div>
          ${extrasHTML}
        `;
      } catch (err) {
        Swal.fire({ icon:'error', title:'Could not load details', text: err.message || 'Please try again.' });
      }
    });

    // Delegated confirm for Cancel
    document.addEventListener('submit', function (e) {
      const form = e.target.closest('.js-confirm');
      if (!form) return;
      e.preventDefault();

      const title = form.dataset.title || 'Are you sure?';
      const text  = form.dataset.text  || '';

      Swal.fire({
        title, text, icon:'warning',
        showCancelButton:true, reverseButtons:true,
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'No'
      }).then((res) => {
        if (res.isConfirmed) form.submit();
      });
    });

    // Toast helper (after redirect from cancel)
    const toast = (icon, msg) => {
      Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:2400, timerProgressBar:true, icon, title:msg });
    };

    @if(session('success')) toast('success', @json(session('success'))); @endif
    @if(session('error'))   toast('error',   @json(session('error')));   @endif
  </script>
@endpush
