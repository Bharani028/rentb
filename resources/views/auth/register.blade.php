@extends('layouts.app')

@push('head')
<style>
  :root { --rb-primary: #4f46e5; } /* indigo-600 */

  .auth-bg{
    background:
      radial-gradient(1200px 600px at 90% -100px, #c7d2fe 0%, transparent 60%),
      radial-gradient(800px 500px at -200px 100%, #e9d5ff 0%, transparent 60%),
      #f8fafc;
  }
  .auth-wrap{
    max-width: 1040px;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(2,6,23,.08), 0 2px 6px rgba(2,6,23,.06);
    border: 1px solid rgba(15,23,42,.06);
    background: #fff;
  }
  .auth-hero{
    background:
      url("{{ asset('images/register.png') }}") center/cover no-repeat,
      linear-gradient(135deg, #1f2937, #0f172a);
    position: relative;
    min-height: 420px;
  }
  .auth-hero::after{
    content:""; position:absolute; inset:0;
    background: linear-gradient(180deg, rgba(0,0,0,.4), rgba(0,0,0,.15));
  }
  .badge-soft{
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.25);
    color:#fff; border-radius:999px; padding:.35rem .7rem; font-weight:600; font-size:.8rem;
  }
  .form-control, .form-select, .input-group-text, .btn { border-radius: 12px; }
  .btn-primary{ --bs-btn-bg: var(--rb-primary); --bs-btn-border-color: var(--rb-primary); }
  .btn-primary:hover{ filter:brightness(.95); }
</style>
@endpush

@section('content')
<div class="auth-bg">
  <div class="container pb-5 pt-3 pt-md-4">

    <div class="row g-0 auth-wrap mx-auto">
      {{-- Left / Visual --}}
      <div class="col-lg-6 auth-hero">
        <div class="position-absolute bottom-0 start-0 end-0 p-4 p-lg-5 text-white" style="z-index:2">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge-soft"><i class="bi bi-person-plus me-1"></i> Easy signup</span>
            <span class="badge-soft"><i class="bi bi-shield-lock me-1"></i> Secure</span>
            <span class="badge-soft"><i class="bi bi-rocket me-1"></i> Free</span>
          </div>
          <h2 class="fw-semibold">Create your account</h2>
          <p class="text-white-50 mb-0">Start posting and booking rentals with {{ config('app.name','RentB') }}.</p>
        </div>
      </div>

      {{-- Right / Form --}}
      <div class="col-lg-6">
        <div class="p-4 p-md-5">

          <h1 class="h4 fw-semibold mb-1">Sign up</h1>
          <p class="text-muted mb-4">
            Already have an account?
            <a href="{{ route('login') }}">Log in</a>
          </p>

          <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            {{-- Name --}}
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input
                  id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                  class="form-control @error('name') is-invalid @enderror">
              </div>
              @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                  id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                  class="form-control @error('email') is-invalid @enderror">
              </div>
              @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Phone number --}}
            <div class="mb-3">
              <label for="phone_number" class="form-label">Phone number</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input
                  id="phone_number"
                  type="tel"
                  name="phone_number"
                  value="{{ old('phone_number') }}"
                  required
                  autocomplete="tel"
                  class="form-control @error('phone_number') is-invalid @enderror">
              </div>
              @error('phone_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                  id="password" type="password" name="password" required autocomplete="new-password"
                  class="form-control @error('password') is-invalid @enderror">
                <button class="btn btn-outline-secondary" type="button" id="togglePwd" title="Show/hide">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-3">
              <label for="password_confirmation" class="form-label">Confirm password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                <input
                  id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                  class="form-control @error('password_confirmation') is-invalid @enderror">
                <button class="btn btn-outline-secondary" type="button" id="togglePwd2" title="Show/hide">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              @error('password_confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            {{-- Terms (optional) --}}
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" value="1" id="terms" required>
              <label class="form-check-label" for="terms">
                I agree to the <a href="{{ url('/terms') }}">Terms</a> and <a href="{{ url('/privacy') }}">Privacy Policy</a>.
              </label>
            </div>

            {{-- Submit --}}
            <button class="btn btn-primary w-100 py-2" type="submit">
              <i class="bi bi-person-check me-1"></i> Register
            </button>
          </form>

          <p class="text-muted small mt-4 mb-0">
            You can change your preferences anytime in your account settings.
          </p>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  // password visibility toggles
  (function () {
    const toggle = (btnId, inputId) => {
      const btn = document.getElementById(btnId);
      if (!btn) return;
      btn.addEventListener('click', function(){
        const input = document.getElementById(inputId);
        const icon  = this.querySelector('i');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.classList.toggle('bi-eye', !show);
        icon.classList.toggle('bi-eye-slash', show);
      });
    };
    toggle('togglePwd', 'password');
    toggle('togglePwd2', 'password_confirmation');
  })();
</script>
@endpush
