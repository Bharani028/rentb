@extends('layouts.app')

@section('title', 'Browse Properties')

@push('head')
<style>
  .filter-card{border:0;border-radius:1rem;box-shadow:0 6px 24px rgba(16,24,40,.06)}
  .card-grid{border:0;border-radius:1rem;overflow:hidden;box-shadow:0 6px 24px rgba(16,24,40,.06)}
  .prop-thumb{height:190px;object-fit:cover;width:100%;background:#f3f5f7}
  .badge-soft{background:#eef2f6;color:#475467;font-weight:600}
  .price-chip{position:absolute;right:.75rem;top:.75rem}
  .featured-chip{position:absolute;left:.75rem;top:.75rem}
  .type-chip{background:#f6f7fb;border-radius:999px;padding:.2rem .55rem;font-size:.75rem}
  .clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
</style>
@endpush

@section('content')
<div class="container my-4">

  {{-- Title + search --}}
  <div class="d-flex align-items-center flex-wrap gap-3 mb-4">
      <div>
          <h2 class="mb-1">Browse Properties</h2>
          <div class="text-muted">Find your next place to stay</div>
      </div>
      <form action="{{ route('properties.browse') }}" method="GET" class="ms-auto d-flex gap-2 flex-grow-1" style="max-width:620px;">
          <input type="search" name="q" class="form-control" placeholder="Search properties, cities, amenities…"
                 value="{{ request('q') }}">
          <button class="btn btn-outline-primary">Search</button>
      </form>
  </div>

  <div class="row g-4">
    {{-- LEFT FILTERS --}}
    <div class="col-lg-3">
      <form action="{{ route('properties.browse') }}" method="GET" class="filter-card p-3 bg-white">
        {{-- keep existing query like search when filtering --}}
        <input type="hidden" name="q" value="{{ request('q') }}">

        <h6 class="mb-3">Property Type</h6>
        @foreach($types as $t)
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="type[]" value="{{ $t->id }}"
                   id="type{{ $t->id }}"
                   {{ in_array($t->id, (array)request('type', [])) ? 'checked' : '' }}>
            <label class="form-check-label" for="type{{ $t->id }}">{{ $t->name }}</label>
          </div>
        @endforeach
        <hr>

        <h6 class="mb-3">Amenities</h6>
        <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
          @foreach($amenities as $a)
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="amenities[]" value="{{ $a->id }}"
                     id="amen{{ $a->id }}"
                     {{ in_array($a->id, (array)request('amenities', [])) ? 'checked' : '' }}>
              <label class="form-check-label" for="amen{{ $a->id }}"><i class="fa {{ $a->icon }}"></i> {{ $a->name }}</label>
            </div>
          @endforeach
        </div>
        <hr>

        <div class="row g-2">
          <div class="col-6">
            <label class="form-label small">Bedrooms</label>
            <select class="form-select" name="bedrooms">
              @php $b = request('bedrooms','any'); @endphp
              <option value="any" {{ $b==='any'?'selected':'' }}>Any</option>
              @for($i=1;$i<=5;$i++)
                <option value="{{ $i }}" {{ (string)$b===(string)$i?'selected':'' }}>{{ $i }}+</option>
              @endfor
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small">Bathrooms</label>
            <select class="form-select" name="bathrooms">
              @php $ba = request('bathrooms','any'); @endphp
              <option value="any" {{ $ba==='any'?'selected':'' }}>Any</option>
              @for($i=1;$i<=5;$i++)
                <option value="{{ $i }}" {{ (string)$ba===(string)$i?'selected':'' }}>{{ $i }}+</option>
              @endfor
            </select>
          </div>
        </div>

        <div class="row g-2 mt-2">
          <div class="col-6">
            <label class="form-label small">Min Price</label>
            <input type="number" name="min_price" class="form-control" value="{{ request('min_price') }}" min="0" step="0.01">
          </div>
          <div class="col-6">
            <label class="form-label small">Max Price</label>
            <input type="number" name="max_price" class="form-control" value="{{ request('max_price') }}" min="0" step="0.01">
          </div>
        </div>

        <div class="mt-2">
          <label class="form-label small">Rent Type</label>
          <select class="form-select" name="rent_type">
            <option value="">Any</option>
            <option value="daily"   {{ request('rent_type')==='daily'?'selected':'' }}>Daily</option>
            <option value="monthly" {{ request('rent_type')==='monthly'?'selected':'' }}>Monthly</option>
          </select>
        </div>

        <div class="d-grid mt-3">
          <button class="btn btn-primary">Apply Filters</button>
        </div>
        <a href="{{ route('properties.browse') }}" class="btn btn-link w-100 mt-1">Reset</a>
      </form>
    </div>

    {{-- RIGHT: RESULTS --}}
    <div class="col-lg-9">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="text-muted">
          {{ $properties->total() }} properties found
        </div>
        <form method="GET" action="{{ route('properties.browse') }}" class="d-flex gap-2">
            {{-- keep all current filters when changing sort --}}
            @foreach(request()->except('sort','page') as $k => $v)
              @if(is_array($v))
                @foreach($v as $sv)
                  <input type="hidden" name="{{ $k }}[]" value="{{ $sv }}">
                @endforeach
              @else
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
              @endif
            @endforeach
            <select name="sort" class="form-select" onchange="this.form.submit()">
              <option value="newest"     {{ $sort==='newest'?'selected':'' }}>Newest</option>
              <option value="oldest"     {{ $sort==='oldest'?'selected':'' }}>Oldest</option>
              <option value="price_low"  {{ $sort==='price_low'?'selected':'' }}>Price: Low → High</option>
              <option value="price_high" {{ $sort==='price_high'?'selected':'' }}>Price: High → Low</option>
            </select>
        </form>
      </div>

      @if($properties->count() === 0)
        <div class="card card-grid p-4 text-center text-muted">No properties match your filters.</div>
      @else
        <div class="row g-4">
          @foreach($properties as $p)
            @php
              $thumb = method_exists($p, 'getFirstMediaUrl') ? $p->getFirstMediaUrl('images', 'thumb') : null;
              if(!$thumb){ $thumb = $p->getFirstMediaUrl('images'); }
              $thumb = $thumb ?: 'https://placehold.co/800x500?text=Property';
              $addr = collect([$p->city, $p->state])->filter()->implode(', ');
              $typeName = $p->type->name ?? '—';
              $per = $p->rent_type === 'daily' ? '/day' : '/month';
            @endphp
            <div class="col-md-6 col-xl-4">
              <div class="card card-grid h-100">
                <div class="position-relative">
                  <img src="{{ $thumb }}" class="prop-thumb" alt="{{ $p->title }}">
                  <span class="badge text-bg-info price-chip">${{ number_format((float)$p->price,2) }}{{ $per }}</span>
                  {{-- Example featured flag (if you later add it) --}}
                  {{-- <span class="badge text-bg-warning featured-chip">Featured</span> --}}
                </div>
                <div class="p-3">
                  <div class="mb-2">
                    <span class="type-chip">{{ $typeName }}</span>
                  </div>
                  <a href="{{ route('properties.show', $p->id) }}" class="stretched-link text-decoration-none">
                    <h6 class="mb-1">{{ $p->title }}</h6>
                  </a>
                  <div class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $addr ?: '—' }}</div>
                  <div class="d-flex gap-3 small text-muted">
                    <div><i class="bi bi-door-closed me-1"></i>{{ $p->bedrooms ?? 0 }} beds</div>
                    <div><i class="bi bi-droplet me-1"></i>{{ $p->bathrooms ?? 0 }} baths</div>
                    <div><i class="bi bi-eye me-1"></i>{{ number_format($p->view_count ?? 0) }}</div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-4">
          {{ $properties->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
