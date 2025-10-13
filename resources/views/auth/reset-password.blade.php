@extends('layouts.app')

@push('head')
<style>
  :root { --rb-primary:#4f46e5; }
  .auth-bg{
    background:
      radial-gradient(1200px 600px at 90% -100px, #c7d2fe 0%, transparent 60%),
      radial-gradient(800px 500px at -200px 100%, #e9d5ff 0%, transparent 60%),
      #f8fafc;
  }
  .auth-card{
    max-width: 640px;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(2,6,23,.08), 0 2px 6px rgba(2,6,23,.06);
    border: 1px solid rgba(15,23,42,.06);
    background: #fff;
  }
  .form-control, .input-group-text, .btn { border-radius: 12px; }
  .btn-primary{ --bs-btn-bg: var(--rb-primary); --bs-btn-border-color: var(--rb-primary); }
  .btn-primary:hover{ filter:brightness(.95); }
</style>
@endpush

@section('content')
<div class="auth-bg">
  <div class="container py-5">
    <div class="mx-auto auth-card p-4 p-md-5">
      <h1 class="h4 fw-semibold mb-2">Reset your password</h1>
      <p class="text-muted mb-4">Enter a new password for your account.</p>

      {{-- Validation summary (optional) --}}
      @if ($errors->any())
        <div class="alert alert-danger rounded-3 py-2 mb-4">
          Please fix the errors below.
        </div>
      @endif

      <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        {{-- Password Reset Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input
              id="email" type="email" name="email"
              value="{{ old('email', $request->email) }}"
              required autocomplete="username" autofocus
              class="form-control @error('email') is-invalid @enderror">
          </div>
          @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        {{-- New Password --}}
        <div class="mb-3">
          <label for="password" class="form-label">New password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
              id="password" type="password" name="password"
              required autocomplete="new-password"
              class="form-control @error('password') is-invalid @enderror">
            <button class="btn btn-outline-secondary" type="button" id="togglePwd" title="Show/hide">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="mb-4">
          <label for="password_confirmation" class="form-label">Confirm password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
            <input
              id="password_confirmation" type="password" name="password_confirmation"
              required autocomplete="new-password"
              class="form-control @error('password_confirmation') is-invalid @enderror">
            <button class="btn btn-outline-secondary" type="button" id="togglePwd2" title="Show/hide">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password_confirmation') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('login') }}" class="small">Back to sign in</a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-arrow-repeat me-1"></i> Reset Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function toggleField(btnId, inputId){
    const btn = document.getElementById(btnId);
    const input = document.getElementById(inputId);
    btn.addEventListener('click', () => {
      const showing = input.type === 'password';
      input.type = showing ? 'text' : 'password';
      const icon = btn.querySelector('i');
      icon.classList.toggle('bi-eye', !showing);
      icon.classList.toggle('bi-eye-slash', showing);
    });
  }
  toggleField('togglePwd',  'password');
  toggleField('togglePwd2', 'password_confirmation');
</script>
@endpush
