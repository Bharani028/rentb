@extends('layouts.app')

@push('head')
<style>
  .owner-wrap{display:grid;grid-template-columns:260px 1fr;gap:1.5rem}
  .owner-sidebar{position:sticky;top:1.25rem;height:calc(100dvh - 2.5rem);padding:1rem;background:#fff;border-radius:1rem;box-shadow:0 6px 24px rgba(16,24,40,.06)}
  .owner-brand{font-weight:700;font-size:1.15rem}
  .owner-nav{margin-top:1rem}
  .owner-nav .section-title{font-size:.8rem;color:#667085;margin:.75rem 1rem}
  .owner-link{display:flex;align-items:center;gap:.6rem;padding:.65rem 1rem;border-radius:.75rem;color:#344054;text-decoration:none;transition:.18s}
  .owner-link:hover{background:#f3f5f7}
  .owner-link.active{background:#e9f2ff;color:#175cd3}
  .owner-link .bi{font-size:1.05rem}
  .owner-content{min-width:0}
  .metric-card{border:0;border-radius:1rem;box-shadow:0 6px 24px rgba(16,24,40,.06)}
  @media (max-width: 992px){
    .owner-wrap{grid-template-columns:1fr}
    .owner-sidebar{position:static;height:auto}
  }
</style>
@endpush

@section('content')
<div class="container my-4">
  <div class="owner-wrap">

    {{-- SIDEBAR --}}
    <aside class="owner-sidebar">
      <div class="d-flex align-items-center justify-content-between">
        <div class="owner-brand">{{ config('app.name', 'RentB') }}</div>
      </div>

      <nav class="owner-nav">
        <div class="section-title">Dashboard</div>
        <a class="owner-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           href="{{ route('dashboard') }}">
          <i class="bi bi-house-door"></i> <span>My Listings</span>
        </a>

        <div class="section-title">Manage</div>
        <a class="owner-link {{ request()->routeIs('owner.bookings.*') ? 'active' : '' }}"
           href="{{ route('owner.bookings.index') }}">
          <i class="bi bi-calendar2-check"></i> <span>Bookings</span>
          @isset($countBookings)
            <span class="ms-auto badge rounded-pill text-bg-light">{{ $countBookings }}</span>
          @endisset
        </a>

        <a class="owner-link {{ request()->routeIs('owner.applications.*') ? 'active' : '' }}"
           href="{{ route('owner.applications.index') }}">
          <i class="bi bi-inbox"></i> <span>My Applications</span>
          @isset($countMyApplications)
            <span class="ms-auto badge rounded-pill text-bg-light">{{ $countMyApplications }}</span>
          @endisset
        </a>

        <div class="section-title">Account</div>
        <a class="owner-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
           href="{{ route('profile.edit') }}">
          <i class="bi bi-person"></i> <span>Profile</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
          @csrf
          <button class="owner-link w-100 text-start" type="submit">
            <i class="bi bi-box-arrow-right"></i> Logout
          </button>
        </form>
      </nav>
    </aside>

    {{-- MAIN --}}
    <main class="owner-content">
      @yield('owner')
    </main>

  </div>
</div>
@endsection
