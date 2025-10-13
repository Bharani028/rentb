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
      <h1 class="h4 fw-semibold mb-2">Forgot your password?</h1>
      <p class="text-muted mb-4">
        No problem—enter your email and we’ll send you a link to reset it.
      </p>

      {{-- Session status (e.g., "We have emailed your password reset link!") --}}
      @if (session('status'))
        <div class="alert alert-success rounded-3 py-2 mb-4">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input
              id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
              class="form-control @error('email') is-invalid @enderror">
          </div>
          @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('login') }}" class="small">Back to sign in</a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i> Email reset link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
