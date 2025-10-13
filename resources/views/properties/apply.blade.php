{{-- resources/views/properties/apply.blade.php --}}
@php($user = auth()->user())

<div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="POST" action="{{ route('applications.store') }}">
      @csrf
      <input type="hidden" name="property_id" value="{{ $property->id }}">

      <div class="modal-header">
        <h5 class="modal-title">Apply for {{ $property->title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          {{-- Start date --}}
          <div class="col-md-6">
            <label for="startDate" class="form-label">Start date</label>
            <input
              type="date"
              id="startDate"
              name="start_date"
              value="{{ old('start_date') }}"
              class="form-control @error('start_date') is-invalid @enderror"
              required>
            @error('start_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- End date --}}
          <div class="col-md-6">
            <label for="endDate" class="form-label">End date</label>
            <input
              type="date"
              id="endDate"
              name="end_date"
              value="{{ old('end_date') }}"
              class="form-control @error('end_date') is-invalid @enderror"
              required>
            @error('end_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- Name --}}
          <div class="col-md-6">
            <label for="appName" class="form-label">Full name</label>
            <input
              type="text"
              id="appName"
              name="name"
              value="{{ old('name', $user->name ?? '') }}"
              class="form-control @error('name') is-invalid @enderror"
              required>
            @error('name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- Email --}}
          <div class="col-md-6">
            <label for="appEmail" class="form-label">Email</label>
            <input
              type="email"
              id="appEmail"
              name="email"
              value="{{ old('email', $user->email ?? '') }}"
              class="form-control @error('email') is-invalid @enderror"
              required>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- Phone --}}
          <div class="col-12">
            <label for="appPhone" class="form-label">Phone</label>
            <input
              type="text"
              id="appPhone"
              name="phone"
              value="{{ old('phone') }}"
              class="form-control @error('phone') is-invalid @enderror">
            @error('phone')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          {{-- Message --}}
          <div class="col-12">
            <label for="appMsg" class="form-label">Message (optional)</label>
            <textarea
              id="appMsg"
              name="message"
              rows="3"
              class="form-control @error('message') is-invalid @enderror"
              placeholder="Introduce yourself…">{{ old('message') }}</textarea>
            @error('message')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" onclick="this.disabled = true; this.form.submit();">
          <i class="bi bi-send me-1"></i> Submit Application
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  // client-side date guards: no past, end >= start
  (function(){
    const sd = document.getElementById('startDate');
    const ed = document.getElementById('endDate');
    if (sd && ed) {
      const today = new Date().toISOString().split('T')[0];
      sd.min = today;
      if (!sd.value) sd.value = ''; // keep old() intact if present
      if (sd.value) ed.min = sd.value; else ed.min = today;
      sd.addEventListener('change', () => {
        ed.min = sd.value || today;
        if (ed.value && sd.value && ed.value < sd.value) ed.value = sd.value;
      });
    }

    // If validation failed and we returned to this property, reopen modal.
    @if($errors->any() && old('property_id') == $property->id)
      document.addEventListener('DOMContentLoaded', function(){
        const m = new bootstrap.Modal(document.getElementById('applyModal'));
        m.show();
      });
    @endif
  })();
</script>
@endpush
