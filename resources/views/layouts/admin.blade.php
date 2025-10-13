<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name').' | Admin')</title>

  {{-- Bootstrap 5 + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  {{-- SweetAlert2 --}}
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  {{-- Optional: your app CSS / Vite --}}
  {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

  <style>
    :root{
      --sidebar-w: 250px;
      --sidebar-bg: #0f172a; /* slate-900 */
      --sidebar-fg: #e2e8f0; /* slate-200 */
      --sidebar-fg-dim:#94a3b8; /* slate-400 */
      --brand:#0d6efd;
    }
    body{background:#f8fafc;}
    .admin-wrapper{display:flex;min-height:100vh;overflow:hidden}
    .sidebar{
      width:var(--sidebar-w);
      background:var(--sidebar-bg);
      color:var(--sidebar-fg);
      position:sticky; top:0; height:100vh; flex-shrink:0;
    }
    .sidebar .brand{
      padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.06);
      font-weight:700; letter-spacing:.3px; display:flex; align-items:center; gap:.5rem
    }
    .sidebar .nav-link{
      color:var(--sidebar-fg-dim);
      padding:.65rem 1rem; border-radius:.5rem; margin:.15rem .5rem; display:flex; align-items:center; gap:.6rem;
    }
    .sidebar .nav-link:hover{background:rgba(255,255,255,.06); color:#fff}
    .sidebar .nav-link.active{background:rgba(13,110,253,.18); color:#fff}
    .sidebar .section-title{font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; padding:.75rem 1rem; color:#64748b}
    .content{flex:1; min-width:0;}
    .topbar{position:sticky; top:0; z-index:1020; background:#fff; border-bottom:1px solid rgba(0,0,0,.06);}
    .metric-card{border:1px solid rgba(0,0,0,.06);border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.06)}
    @media (max-width: 991.98px){
      .sidebar{position:fixed; left:-270px; transition:left .2s ease}
      .sidebar.show{left:0}
      .content{width:100%}
    }
  </style>

  @stack('styles')
</head>
<body>
<div id="app" class="admin-wrapper">

  {{-- Sidebar --}}
  <nav id="sidebar" class="sidebar">
    <div class="brand">
      <i class="bi bi-speedometer2 text-primary fs-4"></i>
      <span>{{ config('app.name') }} Admin</span>
    </div>

    <div class="section-title">Main</div>
    <ul class="nav flex-column mb-2">
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">
          <i class="bi bi-house-door"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
           href="{{ route('admin.users.index') }}">
          <i class="bi bi-people"></i> Users
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.properties.*') ? 'active' : '' }}"
           href="{{ route('admin.properties.index') }}">
          <i class="bi bi-buildings"></i> Properties
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
           href="{{ route('admin.bookings.index') }}">
          <i class="bi bi-calendar-check"></i> Bookings
        </a>
      </li>
    </ul>

    <div class="section-title">Account</div>
    <ul class="nav flex-column mb-3">
      @if(session()->has('impersonator_id'))
        <li class="nav-item px-3">
          <div class="alert alert-warning py-2 mb-2">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-person-badge"></i>
              <span>Impersonating</span>
            </div>
          </div>
        </li>
      @endif
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}" class="px-2" id="logout-form">
          @csrf
          <button type="button" class="nav-link w-100 text-start btn btn-link p-0 js-logout">
            <i class="bi bi-box-arrow-right"></i> Logout
          </button>
        </form>
      </li>
    </ul>

    <div class="text-muted small px-3 pb-3 mt-auto">
      &copy; {{ date('Y') }} {{ config('app.name') }}
    </div>
  </nav>

  {{-- Main content --}}
  <div class="content d-flex flex-column w-100">
    {{-- Topbar --}}
    <div class="topbar">
      <div class="container-fluid px-3 py-2 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggle" type="button">
            <i class="bi bi-list"></i>
          </button>
          <div class="fw-semibold">@yield('page_title')</div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="text-muted small d-none d-md-block">
            {{ auth()->user()->name ?? 'Admin' }}
          </div>
          <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode(auth()->user()->name ?? 'Admin') }}"
               alt="avatar" width="32" height="32" class="rounded-circle border">
        </div>
      </div>
    </div>

    {{-- Page body --}}
    <main class="flex-grow-1">
      @yield('content')
    </main>
  </div>
</div>

{{-- jQuery (needed by DataTables) --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
{{-- Bootstrap --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('sidebar');
    btn?.addEventListener('click', () => sb?.classList.toggle('show'));

    // Close sidebar on route link click (mobile)
    document.querySelectorAll('.sidebar .nav-link').forEach(a => {
      a.addEventListener('click', () => {
        if (window.innerWidth < 992) sb?.classList.remove('show');
      });
    });

    // Logout confirmation
    document.querySelector('.js-logout').addEventListener('click', function (e) {
      e.preventDefault();
      const form = document.getElementById('logout-form');
      Swal.fire({
        icon: 'warning',
        title: 'Log out?',
        html: '<div class="text-muted">Are you sure you want to log out?</div>',
        showCancelButton: true,
        confirmButtonText: 'Log out',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
      }).then(res => {
        if (res.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>

@stack('scripts')
</body>
</html>