@extends('layouts.app')

@push('head')
  {{-- Swiper for sleek horizontal carousels --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"/>
  <style>
    .hero {
      background: radial-gradient(1200px 600px at 10% -10%, #eef6ff, transparent),
                  radial-gradient(800px 400px at 90% -20%, #fef3f2, transparent),
                  #fff;
      border-radius: 0 0 24px 24px;
      box-shadow: 0 6px 24px rgba(0,0,0,.06) inset;
    }
    .hero .search-wrap { max-width: 760px; }
    .category-tile {
      border:1px solid rgba(0,0,0,.06); border-radius:16px; padding:16px;
      background:#fff; transition:.2s; height:100%;
    }
    .category-tile:hover { transform: translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.08); }
    .benefit {
      border:1px solid rgba(0,0,0,.06); border-radius:16px; background:#fff; padding:18px;
    }
    .card.property {
      border:1px solid rgba(0,0,0,.06); border-radius:16px; overflow:hidden;
    }
    .property .thumb { height: 180px; object-fit: cover; width: 100%; }
    .pill { border-radius:999px; padding:.35rem .65rem; font-size:.8rem; background:#f1f5f9; }
    .cta-banner {
      background: linear-gradient(135deg,#0ea5e9 0%,#6366f1 100%);
      color:#fff; border-radius:20px;
    }
    .swiper-button-next, .swiper-button-prev { color:#111827; }
  </style>
@endpush

@section('content')
  {{-- HERO --}}
  <section class="hero py-5">
    <div class="container text-center">
      <h1 class="display-5 fw-semibold mb-2">Rent homes you’ll love in minutes</h1>
      <p class="text-muted mb-4">Flexible stays, transparent pricing, and curated listings.</p>

      <form class="mx-auto search-wrap" action="{{ route('properties.browse') }}" method="get">
        <div class="input-group input-group-lg shadow-sm">
          <span class="input-group-text bg-white border-0"><i class="bi bi-geo-alt"></i></span>
          <input name="q" type="text" class="form-control border-0" placeholder="Search by city, area, landmark…">
          <button class="btn btn-primary px-4"><i class="bi bi-search me-1"></i>Search</button>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
          @foreach($types->take(6) as $t)
            <a href="{{ route('properties.browse', ['type[]' => $t->id]) }}" class="pill text-decoration-none text-dark">{{ $t->name }}</a>
          @endforeach
        </div>
      </form>

      <div class="mt-3 small text-muted">Trusted by renters across multiple cities</div>
    </div>
  </section>

  {{-- BENEFITS STRIP --}}
  <section class="container my-4">
    <div class="row g-3">
      <div class="col-6 col-md-3"><div class="benefit h-100"><i class="bi bi-stars me-2 text-primary"></i> Verified Listings</div></div>
      <div class="col-6 col-md-3"><div class="benefit h-100"><i class="bi bi-currency-rupee me-2 text-primary"></i> Transparent Pricing</div></div>
      <div class="col-6 col-md-3"><div class="benefit h-100"><i class="bi bi-clock me-2 text-primary"></i> Flexible Stays</div></div>
      <div class="col-6 col-md-3"><div class="benefit h-100"><i class="bi bi-life-preserver me-2 text-primary"></i> 24/7 Support</div></div> 
    </div>
  </section>

  {{-- CATEGORIES (like “Shop by Room/Category”) --}}
<section class="container my-4">
  <h4 class="mb-3">Browse by Category</h4>
  <div class="row g-3">
    @foreach($types->take(8) as $t)
      @php
        // Make a filename from the type name, e.g. "House" -> "house.jpg"
        $slug = \Illuminate\Support\Str::slug($t->name);               // house, room, tent, etc.
        $rel  = "images/{$slug}.jpg";                                  // relative to /public
        $abs  = public_path($rel);
        $src  = file_exists($abs) ? asset($rel) : asset('images/placeholder.jpg');
      @endphp

      <div class="col-6 col-md-3">
        <a class="text-decoration-none text-dark"
           href="{{ route('properties.browse', ['type[]' => $t->id]) }}">
          <div class="category-tile">
            <div class="ratio ratio-16x9 mb-2">
              <img
                src="{{ $src }}"
                alt="{{ $t->name }}"
                class="w-100 h-100 rounded"
                style="object-fit:cover;"
                loading="lazy"
                width="600" height="400">
            </div>
            <div class="fw-semibold">{{ $t->name }}</div>
            <div class="text-muted small">Explore {{ $t->name }} rentals</div>
          </div>
        </a>
      </div>
    @endforeach
  </div>
</section>

  {{-- FEATURED CAROUSEL --}}
  <section class="container my-5">
    <div class="d-flex justify-content-between align-items-end mb-2">
      <h4 class="mb-0">Featured in @if($city) {{ $city }} @else Your City @endif</h4>
      <a href="{{ route('properties.browse') }}" class="text-decoration-none">View all</a>
    </div>

    <div class="swiper" id="swiperFeatured">
      <div class="swiper-wrapper">
        @foreach($featured as $p)
          <div class="swiper-slide" style="width: 280px;">
            @include('layouts.partials.property_card', ['p' => $p])
          </div>
        @endforeach
      </div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
  </section>

  {{-- BANNER / USP --}}
  <section class="container my-5">
    <div class="cta-banner p-4 p-md-5 d-lg-flex align-items-center justify-content-between">
      <div>
        <h3 class="fw-semibold mb-1">Move in faster with instant approvals</h3>
        <p class="mb-0">Simple application flow, status tracking, and owner chat.</p>
      </div>
      <a href="{{ route('properties.browse') }}" class="btn btn-light mt-3 mt-lg-0">Start browsing</a>
    </div>
  </section>

  {{-- TRENDING CAROUSEL --}}
  <section class="container my-5">
    <div class="d-flex justify-content-between align-items-end mb-2">
      <h4 class="mb-0">Trending now</h4>
      <a href="{{ route('properties.browse', ['sort' => 'price_low']) }}" class="text-decoration-none">Best deals</a>
    </div>

    <div class="swiper" id="swiperTrending">
      <div class="swiper-wrapper">
        @foreach($trending as $p)
          <div class="swiper-slide" style="width: 280px;">
            @include('layouts.partials.property_card', ['p' => $p])
          </div>
        @endforeach
      </div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
    </div>
  </section>

  {{-- FAQ / FOOTER CTA --}}
  <section class="container my-5">
    <div class="row g-4">
      <div class="col-lg-6">
        <h4 class="mb-3">FAQs</h4>
        <div class="accordion" id="faq">
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#f1">How do I book?</button></h2>
            <div id="f1" class="accordion-collapse collapse show" data-bs-parent="#faq"><div class="accordion-body">Open a property, hit Apply, submit your dates and details. Owners respond quickly.</div></div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#f2">What fees are charged?</button></h2>
            <div id="f2" class="accordion-collapse collapse" data-bs-parent="#faq"><div class="accordion-body">No hidden fees. See price per month/day on each property; taxes extra if applicable.</div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="p-4 border rounded-4 h-100">
          <h4>List your property</h4>
          <p class="text-muted">Reach quality tenants fast. It’s free to post.</p>
          <a href="{{ route('properties.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Post a listing</a>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script>
    new Swiper('#swiperFeatured', {
      slidesPerView: 1.15, spaceBetween: 12,
      breakpoints: { 576:{slidesPerView:2.2}, 768:{slidesPerView:3}, 992:{slidesPerView:4}, 1200:{slidesPerView:5} },
      navigation:{ nextEl:'#swiperFeatured .swiper-button-next', prevEl:'#swiperFeatured .swiper-button-prev' }
    });
    new Swiper('#swiperTrending', {
      slidesPerView: 1.15, spaceBetween: 12,
      breakpoints: { 576:{slidesPerView:2.2}, 768:{slidesPerView:3}, 992:{slidesPerView:4}, 1200:{slidesPerView:5} },
      navigation:{ nextEl:'#swiperTrending .swiper-button-next', prevEl:'#swiperTrending .swiper-button-prev' }
    });
  </script>
@endpush
