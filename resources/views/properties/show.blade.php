@extends('layouts.app')

@section('content')
<div class="container my-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="mb-1">{{ $property->title }}</h2>
            <div class="text-muted mb-1">
                {{ collect([$property->door_no, $property->street, $property->district, $property->city, $property->state, $property->country, $property->postal_code])->filter()->implode(', ') }}
            </div>
            <div class="text-muted small">
                <i class="bi bi-eye me-1"></i> {{ number_format($property->view_count) }} views
            </div>
        </div>

        {{-- OWNER ACTIONS --}}
        @if($isOwner)
            <div class="d-flex gap-2">
                <a href="{{ route('properties.edit', $property->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <form action="{{ route('properties.destroy', $property->id) }}" method="POST" onsubmit="return confirmDelete(event)">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        @else
            {{-- NON-OWNER ACTIONS --}}
            @auth
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal">
                    <i class="bi bi-send me-1"></i> Apply
                </button>
            @else
                <a class="btn btn-primary" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login to Apply
                </a>
            @endauth
        @endif
    </div>

    {{-- Gallery --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            @php
                // Cap to 10 images for UI
                $media = $property->getMedia('images')->take(10);
                $count = $media->count();
            @endphp

            @if($count)
                <div class="row g-2">
                    <div class="col-lg-8">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal" data-index="0">
                            <div class="gallery-main">
                                <img src="{{ $media[0]->getUrl() }}" alt="cover">
                            </div>
                        </a>
                    </div>

                    <div class="col-lg-4">
                        <div class="row g-2">
                            {{-- Thumbs 1..4 (indexes 1..4) --}}
                            @foreach($media->slice(1)->take(4) as $i => $m)
                                <div class="col-6">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal" data-index="{{ $i+1 }}">
                                        <div class="gallery-thumb">
                                            <img src="{{ $m->getUrl() }}" alt="thumb {{ $i+1 }}">
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Extra row for images 6..10 (indexes 5..9) --}}
                @if($count > 5)
                    <div class="mt-2">
                        <div class="row g-2 row-cols-2 row-cols-md-3 row-cols-lg-5">
                            @foreach($media->slice(5)->take(5) as $i => $m)
                                <div class="col">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#galleryModal" data-index="{{ $i + 5 }}">
                                        <div class="gallery-thumb gallery-thumb-sm">
                                            <img src="{{ $m->getUrl() }}" alt="thumb extra {{ $i + 5 }}">
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Modal --}}
                <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <div id="carouselPhotos" class="carousel slide" data-bs-ride="false">
                                    <div class="carousel-inner">
                                        @foreach($media as $i => $m)
                                            <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                                <img src, src="{{ $m->getUrl() }}" class="d-block w-100" alt="photo {{ $i+1 }}">
                                            </div>
                                        @endforeach
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <div class="text-muted small">Photos: {{ $count }}</div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-images fs-1 d-block mb-2"></i>
                    No photos uploaded.
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Overview</h5>
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">Type</div>
                            <div class="fw-semibold">{{ $property->type->name ?? '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">Price</div>
                            <div class="fw-semibold">
                                {{ number_format((float)$property->price, 2) }}
                                <span class="text-muted">{{ $property->rent_type === 'daily' ? '/day' : '/month' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">Availability</div>
                            <div class="fw-semibold">
                                {{ $property->available_from ?: '—' }}
                                @if($property->available_to) – {{ $property->available_to }} @endif
                            </div>
                        </div>

                        <div class="col-6 col-md-4"><div class="text-muted small">Bedrooms</div><div class="fw-semibold">{{ $property->bedrooms ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">Bathrooms</div><div class="fw-semibold">{{ $property->bathrooms ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">Floors</div><div class="fw-semibold">{{ $property->floors ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">Kitchen</div><div class="fw-semibold">{{ $property->kitchen ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">Hall</div><div class="fw-semibold">{{ $property->hall ?? '—' }}</div></div>
                        <div class="col-6 col-md-4"><div class="text-muted small">Balcony</div><div class="fw-semibold">{{ $property->balcony ?? '—' }}</div></div>

                        <div class="col-6 col-md-4">
                            <div class="text-muted small">Parking</div>
                            <div class="fw-semibold">
                                @if($property->parking)
                                    <span class="badge bg-success-subtle text-success">Available</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">No</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <div class="text-muted small">Status</div>
                            @php
                                $cls = match($property->status){
                                    'active' => 'badge-soft-success',
                                    'pending' => 'badge-soft-warning',
                                    'inactive' => 'badge-soft-secondary',
                                    'rejected' => 'badge-soft-danger',
                                    default => 'badge-soft-secondary'
                                };
                            @endphp
                            <div class="fw-semibold"><span class="badge {{ $cls }} text-capitalize">{{ $property->status }}</span></div>
                        </div>

                    </div>

                    <hr class="my-4">
                    <h5 class="mb-2">Description</h5>
                    <p class="mb-0">{{ $property->description ?: '—' }}</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Amenities</h5>
                    @if($property->amenities->count())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($property->amenities as $a)
                                <span class="badge rounded-pill bg-light text-dark">
                                    @if($a->icon) <i class="fa {{ $a->icon }} me-1"></i>@endif
                                    {{ $a->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">No amenities listed.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- MAP CARD (shown above Quick Info) --}}
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Location</h5>

                    @if($property->latitude && $property->longitude)
                        <div id="view-map"
                             style="height: 260px; border-radius: .75rem; overflow: hidden; border: 1px solid rgba(15,23,42,.08)"></div>

                        <div class="d-flex gap-2 mt-3">
                            <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"
                               href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}">
                                <i class="bi bi-geo-alt me-1"></i> Open in Google Maps
                            </a>
                            <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener"
                               href="https://www.openstreetmap.org/?mlat={{ $property->latitude }}&mlon={{ $property->longitude }}#map=16/{{ $property->latitude }}/{{ $property->longitude }}">
                                OpenStreetMap
                            </a>
                        </div>
                    @else
                        <div class="text-muted">
                            Location not set for this property.
                            @if($isOwner)
                                <div class="mt-2">
                                    <a href="{{ route('properties.edit', $property->id) }}" class="btn btn-sm btn-outline-primary">
                                        Add map location
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Contact</h5>
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <i class="bi bi-telephone me-1 text-primary"></i>
                        <span class="fw-semibold">{{ $property->phone_number ?: 'Not provided' }}</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Quick Info</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2 text-primary"></i>{{ $property->city }}, {{ $property->state }}</li>
                        <li class="mb-2"><i class="bi bi-calendar-event me-2 text-primary"></i>Created: {{ $property->created_at?->format('d M Y') }}</li>
                        <li class="mb-2"><i class="bi bi-sliders me-2 text-primary"></i>Type: {{ $property->type->name ?? '—' }}</li>
                    </ul>
                </div>
            </div>

            {{-- Back only for owner --}}
            @if($isOwner)
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Apply Modal (non-owner) --}}
@auth
  @if(!$isOwner)
    @include('properties.apply', ['property' => $property])
  @endif
@endauth

@endsection

@push('head')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="" />
<style>
    .gallery-main img { width:100%; height:420px; object-fit:cover; border-radius:16px; }
    .gallery-thumb img { width:100%; height:200px; object-fit:cover; border-radius:12px; }
    .badge-soft-success{ background:#e9f7ef; color:#198754; }
    .badge-soft-warning{ background:#fff4e5; color:#b26a00; }
    .badge-soft-danger{  background:#fdecee; color:#dc3545; }
    .badge-soft-secondary{background:#eef2f5; color:#4b5563;}
</style>
@endpush

@push('scripts')
<script>
    const galleryModal = document.getElementById('galleryModal');
    if (galleryModal) {
        galleryModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger) return;
            const index = parseInt(trigger.getAttribute('data-index') || '0', 10);
            const carousel = bootstrap.Carousel.getOrCreateInstance('#carouselPhotos', { interval: false, ride: false });
            carousel.to(index);
        });
    }

    function confirmDelete(e){
        if (window.Swal) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete property?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33'
            }).then(r => { if (r.isConfirmed) e.target.submit(); });
            return false;
        }
        return confirm('Delete property?');
    }
    window.confirmDelete = confirmDelete;
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

@if($property->latitude && $property->longitude)
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = {{ (float) $property->latitude }};
        const lng = {{ (float) $property->longitude }};
        const map = L.map('view-map', { zoomControl: true, attributionControl: true }).setView([lat, lng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map);
    });
</script>
@endif
@endpush