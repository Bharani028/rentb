@extends('layouts.app')

@push('head')
<style>
  :root { --rb-primary: #4f46e5; } /* indigo-600 */

  /* page bg */
  .auth-bg {
    background:
      radial-gradient(1200px 600px at 90% -100px, #c7d2fe 0%, transparent 60%),
      radial-gradient(800px 500px at -200px 100%, #e9d5ff 0%, transparent 60%),
      #f8fafc;
  }

  /* card / shell */
  .auth-wrap {
    max-width: 1040px;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(2,6,23,.08), 0 2px 6px rgba(2,6,23,.06);
    border: 1px solid rgba(15,23,42,.06);
    background: #fff;
  }

  /* left hero panel */
  .auth-hero {
    background:
      url("{{ asset('images/login.jpg') }}") center/cover no-repeat,
      linear-gradient(135deg, #1f2937, #0f172a);
    position: relative;
    min-height: 420px;
  }
  .auth-hero::after {
    content: "";
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,.4), rgba(0,0,0,.15));
  }
  .badge-soft {
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.25);
    color: #fff;
    border-radius: 999px;
    padding: .35rem .7rem;
    font-weight: 600;
    font-size: .8rem;
  }

  /* buttons */
  .btn-primary {
    --bs-btn-bg: var(--rb-primary);
    --bs-btn-border-color: var(--rb-primary);
  }
  .btn-primary:hover { filter: brightness(.95); }

  /* round inputs nicely */
  .form-control, .form-select, .input-group-text, .btn {
    border-radius: 12px;
  }
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
            <span class="badge-soft"><i class="bi bi-shield-check me-1"></i> Secure login</span>
            <span class="badge-soft"><i class="bi bi-lightning me-1"></i> Fast</span>
            <span class="badge-soft"><i class="bi bi-check2-circle me-1"></i> Verified</span>
          </div>
          <h2 class="fw-semibold">Welcome back</h2>
          <p class="text-white-50 mb-0">Manage your listings, applications & messages in one place.</p>
        </div>
      </div>

      {{-- Right / Form --}}
      <div class="col-lg-6">
        <div class="p-4 p-md-5">

          {{-- Session status --}}
          @if (session('status'))
            <div class="alert alert-success rounded-3 py-2">{{ session('status') }}</div>
          @endif

          <h1 class="h4 fw-semibold mb-1">Sign in</h1>
          <p class="text-muted mb-4">
            Don’t have an account?
            @if (Route::has('register'))
              <a href="{{ route('register') }}">Create one</a>
            @endif
          </p>

          <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                  id="email" type="email" name="email"
                  value="{{ old('email') }}" required autocomplete="username" autofocus
                  class="form-control @error('email') is-invalid @enderror">
              </div>
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                  id="password" type="password" name="password" required autocomplete="current-password"
                  class="form-control @error('password') is-invalid @enderror">
                <button class="btn btn-outline-secondary" type="button" id="togglePwd" title="Show/hide">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            {{-- Remember + Forgot --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">Remember me</label>
              </div>
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
              @endif
            </div>

            {{-- Submit --}}
            <button class="btn btn-primary w-100 py-2" type="submit">
              <i class="bi bi-box-arrow-in-right me-1"></i> Log in
            </button>
          </form>

          <p class="text-muted small mt-4 mb-0">
            By continuing, you agree to {{ config('app.name','RentB') }}’s
            <a href="{{ url('/terms') }}">Terms</a> and <a href="{{ url('/privacy') }}">Privacy Policy</a>.
          </p>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  // password visibility
  (function () {
    const btn = document.getElementById('togglePwd');
    if (!btn) return;
    btn.addEventListener('click', function(){
      const input = document.getElementById('password');
      const icon  = this.querySelector('i');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.classList.toggle('bi-eye', !show);
      icon.classList.toggle('bi-eye-slash', show);
    });
  })();
</script>
@endpush
