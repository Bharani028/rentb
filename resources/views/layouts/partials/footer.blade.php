{{-- resources/views/partials/footer.blade.php --}}
@php
  use Illuminate\Support\Facades\Route;
  $newsletterAction = Route::has('newsletter.subscribe') ? route('newsletter.subscribe') : '#';
  $browseUrl        = Route::has('properties.browse')    ? route('properties.browse')    : '#';
  $loginUrl         = Route::has('login')                 ? route('login')                 : '#';
  $registerUrl      = Route::has('register')              ? route('register')              : '#';
  $appsUrl          = Route::has('owner.applications.index') ? route('owner.applications.index') : '#';
  $postUrl          = Route::has('properties.create')     ? route('properties.create')     : '#';
  $dashboardUrl     = Route::has('dashboard')             ? route('dashboard')             : '#';
  $bookingsUrl      = Route::has('owner.bookings.index')  ? route('owner.bookings.index')  : '#';
  $profileUrl       = Route::has('profile.edit')          ? route('profile.edit')          : '#';
@endphp

<footer class="site-footer pt-5 border-top">
  <div class="container">
    <div class="row g-4 align-items-start pb-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <img src="{{ asset('logos/blogo.jpg') }}" alt="Brand" width="58" height="38" />
          <span class="fw-semibold fs-5">{{ config('app.name', 'RentB') }}</span>
        </div>
        <p class="text-muted mb-3">Find great rentals, fast. Verified listings, simple applications, and secure payments.</p>
        <div class="d-flex gap-2">
          <a class="text-decoration-none text-muted lh-1 fs-5" href="https://www.instagram.com/bharani2802/" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a class="text-decoration-none text-muted lh-1 fs-5" href="https://www.linkedin.com/in/bharani-s-331762215/" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>

      <div class="col-lg-5 order-lg-3">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-envelope-open"></i>
              <h6 class="mb-0">Subscribe for updates</h6>
            </div>
            <form method="POST" action="{{ $newsletterAction }}" class="row g-2" @if($newsletterAction==='#') onsubmit="return false" @endif>
              @csrf
              <div class="col-12 col-md">
                <label for="nlEmail" class="visually-hidden">Email</label>
                <input id="nlEmail" type="email" name="email" class="form-control" placeholder="you@example.com" required>
              </div>
              <div class="col-12 col-md-auto">
                <button class="btn btn-primary w-100" @if($newsletterAction==='#') disabled title="Newsletter route not configured" @endif>Subscribe</button>
              </div>
            </form>
            @if(session('newsletter_ok'))
              <div class="small text-success mt-2">{{ session('newsletter_ok') }}</div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-lg-3 order-lg-2">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi bi-headset"></i>
          <h6 class="mb-0">We’re here to help</h6>
        </div>
        <ul class="list-unstyled small mb-0">
          <li class="mb-1"><i class="bi bi-telephone me-2 text-primary"></i><a class="text-decoration-none text-muted" href="tel:+91-00000-00000">+91 00000 00000</a></li>
          <li class="mb-1"><i class="bi bi-envelope me-2 text-primary"></i><a class="text-decoration-none text-muted" href="mailto:exampleemail@rentb.sb">exampleemail@rentb.sb</a></li>
          <li><i class="bi bi-geo-alt me-2 text-primary"></i><span class="text-muted">Some Where in this world</span></li>
        </ul>
      </div>
    </div>

    <hr class="my-0">

    <div class="row g-4 py-4">
      <div class="col-6 col-md-3">
        <h6 class="text-dark fw-semibold mb-2">Renters</h6>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="{{ $browseUrl }}" class="link-secondary text-decoration-none">Browse properties</a></li>
          <li class="mb-1"><a href="{{ $loginUrl }}" class="link-secondary text-decoration-none">Login</a></li>
          <li class="mb-1"><a href="{{ $registerUrl }}" class="link-secondary text-decoration-none">Create account</a></li>
          <li><a href="{{ $appsUrl }}" class="link-secondary text-decoration-none">My applications</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-3">
        <h6 class="text-dark fw-semibold mb-2">Owners</h6>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="{{ $postUrl }}" class="link-secondary text-decoration-none">Post a property</a></li>
          <li class="mb-1"><a href="{{ $dashboardUrl }}" class="link-secondary text-decoration-none">Owner dashboard</a></li>
          <li class="mb-1"><a href="{{ $bookingsUrl }}" class="link-secondary text-decoration-none">Bookings</a></li>
          <li><a href="{{ $profileUrl }}" class="link-secondary text-decoration-none">Profile</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-3">
        <h6 class="text-dark fw-semibold mb-2">Company</h6>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="/" class="link-secondary text-decoration-none">Home</a></li>
          <li class="mb-1"><a href="contact" class="link-secondary text-decoration-none">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-3">
        <h6 class="text-dark fw-semibold mb-2">Legal</h6>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="#" class="link-secondary text-decoration-none">Terms of Service</a></li>
          <li class="mb-1"><a href="#" class="link-secondary text-decoration-none">Privacy Policy</a></li>
          <li class="mb-1"><a href="#" class="link-secondary text-decoration-none">Refunds</a></li>
          <li><a href="#" class="link-secondary text-decoration-none">Cookie Policy</a></li>
        </ul>
      </div>
    </div>

    <hr class="my-0">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3 gap-2">
      <div class="small text-muted order-2 order-md-1">© {{ date('Y') }} {{ config('app.name','RentB') }}. All rights reserved.</div>
      <div class="d-flex align-items-center gap-2 order-1 order-md-2">

        <div class="vr d-none d-md-block"></div>
        <a href="#" class="small link-secondary text-decoration-none">Sitemap</a>
      </div>
    </div>
  </div>
</footer>

@push('head')
<style>
  .site-footer { background:#fff; }
  .site-footer .link-secondary:hover { color:#0d6efd !important; }
  .site-footer .card { background:#fff; }
  @media (prefers-color-scheme: dark) {
    .site-footer { background:#0b0f14; border-color:#10161b; }
    .site-footer, .site-footer .text-muted, .site-footer .link-secondary { color:#9aa4af !important; }
    .site-footer .card { background:#0f141a; }
    .site-footer .border-top, .site-footer hr, .site-footer .vr { border-color:#1b2430 !important; }
  }
</style>
@endpush
