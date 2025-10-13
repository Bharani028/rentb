@php
    $appName   = config('app.name', 'RentB');
    $homeUrl   = url('/');
    $browseUrl = route('properties.browse');
    // Use your named route if available; otherwise fall back to /contact
    $contactUrl = \Illuminate\Support\Facades\Route::has('contact.create')
        ? route('contact.create')
        : url('/contact');
@endphp

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm minimal-nav">
  <div class="container">
    {{-- Brand (left) --}}
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ auth()->check() ? $browseUrl : $homeUrl }}">
      <span class="brand-mark d-inline-flex align-items-center justify-content-center">
        <i class="bi bi-house-heart"></i>
      </span>
      <span>{{ $appName }}</span>
    </a>

    {{-- Toggler --}}
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    {{-- Collapsible area --}}
    <div class="collapse navbar-collapse" id="mainNav">
      {{-- CENTERED main menu --}}
      <ul class="navbar-nav justify-content-lg-center mx-lg-auto my-2 my-lg-0">
        <li class="nav-item">
          <a class="nav-link link-pill {{ request()->is('/') ? 'active' : '' }}" href="{{ $homeUrl }}">
            <i class="bi bi-house-door me-1"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link link-pill {{ request()->routeIs('properties.browse') ? 'active' : '' }}" href="{{ $browseUrl }}">
            <i class="bi bi-search me-1"></i> Browse Properties
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link link-pill {{ request()->routeIs('contact') || request()->is('contact') ? 'active' : '' }}" href="{{ $contactUrl }}">
            <i class="bi bi-envelope me-1"></i> Contact Us
          </a>
        </li>
      </ul>

      {{-- RIGHT auth area --}}
      <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-2">
        @auth
          <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 px-2" href="#" id="userMenu" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
              <span class="avatar-circle">{{ strtoupper(mb_substr(auth()->user()->name ?? 'U',0,1)) }}</span>
              <i class="bi bi-caret-down-fill small"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3" aria-labelledby="userMenu">
              <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-columns-gap me-2"></i>Dashboard</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                </form>
              </li>
            </ul>
          </li>
        @else
          <li class="nav-item"><a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 w-lg-auto">Login</a></li>
          <li class="nav-item"><a href="{{ route('register') }}" class="btn btn-primary w-100 w-lg-auto">Register</a></li>
        @endauth
      </ul>
    </div>
  </div>
</nav>

@push('head')
<style>
  .minimal-nav { border-bottom: 1px solid #f0f2f5; }
  .brand-mark{ width:36px;height:36px;border-radius:10px;background:#eef4ff;color:#175cd3;font-size:1.1rem; }
  .link-pill{ border-radius:12px;padding:.5rem .75rem;font-weight:500;color:#344054; }
  .link-pill:hover{ background:#f5f7fa;color:#1f2a37; }
  .link-pill.active{ background:#eef4ff;color:#175cd3; }
  .avatar-circle{
    width:34px;height:34px;border-radius:50%;background:#eceff3;color:#344054;
    display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;
  }
  @media (max-width: 991.98px){
    #mainNav .navbar-nav{ gap:.25rem; }
    .navbar .btn{ margin-top:.25rem; }
  }
</style>
@endpush
