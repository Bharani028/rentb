@extends('layouts.app')

@section('content')
<div class="rb-page-bg">
  <div class="container py-5 my-2">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow-lg border-0 rounded-4 rb-card">
          {{-- Header --}}
          <div class="card-header text-white rounded-top-4 py-3"
               style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;">
            <h4 class="mb-0 text-center fw-semibold">
              <i class="bi bi-pencil-square me-2"></i> Edit Property
            </h4>
          </div>

          <div class="card-body p-4 p-md-5">
            {{-- Toast Container --}}
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
              <div id="imageLimitToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                  <div class="toast-body fw-semibold">
                    You can upload a maximum of 10 images total. Please remove some images or select fewer files.
                  </div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>
              <div id="locationNotFoundToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                  <div class="toast-body fw-semibold">
                    Location not found. Try being more specific.
                  </div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>
              <div id="searchErrorToast" class="toast align-items-center text-white bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                  <div class="toast-body fw-semibold">
                    Search error. Please try again.
                  </div>
                  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
              </div>
            </div>

            <form action="{{ route('properties.update', $property) }}" method="POST" enctype="multipart/form-data" novalidate>
              @csrf
              @method('PUT')

              {{-- Title & Price --}}
              <div class="row mb-4">
                <div class="col-md-8">
                  <label class="form-label fw-semibold" for="title">Property Title</label>
                  <input id="title" type="text" name="title"
                         class="form-control @error('title') is-invalid @enderror"
                         placeholder="E.g. Spacious 2BHK Apartment"
                         value="{{ old('title', $property->title) }}" required>
                  @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="price">Price (₹ / $)</label>
                  <input id="price" type="number" step="0.01" name="price"
                         class="form-control @error('price') is-invalid @enderror"
                         placeholder="Eg. 1200" value="{{ old('price', $property->price) }}" required>
                  @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Rent Type & Property Type & Phone Number --}}
              <div class="row mb-4">
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="phone_number">Phone Number</label>
                  <input type="text" name="phone_number" id="phone_number"
                         class="form-control @error('phone_number') is-invalid @enderror"
                         value="{{ old('phone_number', $property->phone_number ?? auth()->user()->phone_number ?? '') }}"
                         autocomplete="tel">
                  @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="rent_type">Rent Type</label>
                  <select id="rent_type" name="rent_type" class="form-select @error('rent_type') is-invalid @enderror" required>
                    <option value="">Select Rent Type</option>
                    <option value="daily"   {{ old('rent_type', $property->rent_type)==='daily'?'selected':'' }}>Daily</option>
                    <option value="monthly" {{ old('rent_type', $property->rent_type)==='monthly'?'selected':'' }}>Monthly</option>
                  </select>
                  @error('rent_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="property_type_id">Property Type</label>
                  <select id="property_type_id" name="property_type_id" class="form-select @error('property_type_id') is-invalid @enderror" required>
                    <option value="">Choose Type</option>
                    @foreach($types as $type)
                      <option value="{{ $type->id }}"
                        {{ (string)old('property_type_id', $property->property_type_id) === (string)$type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('property_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Rooms (each input wrapped with data-field="...") --}}
              <div class="row mb-4" id="roomFieldset">
                @foreach(['bedrooms'=>'Bedrooms','bathrooms'=>'Bathrooms','kitchen'=>'Kitchen','balcony'=>'Balcony','hall'=>'Hall','floors'=>'Floors'] as $field => $label)
                  <div class="col-md-2 col-6 mb-3 mb-md-0" data-field="{{ $field }}">
                    <label class="form-label fw-semibold" for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" type="number" min="0" name="{{ $field }}"
                           class="form-control @error($field) is-invalid @enderror"
                           value="{{ old($field, $property->$field) }}">
                    @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                @endforeach
              </div>

              {{-- Address --}}
              <h5 class="fw-semibold mt-2 mb-3"><i class="bi bi-geo-alt me-1"></i> Address Details</h5>
              <div class="row mb-3">
                <div class="col-md-3">
                  <input type="text" name="door_no" class="form-control @error('door_no') is-invalid @enderror"
                         placeholder="Door No" value="{{ old('door_no', $property->door_no) }}">
                  @error('door_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-9">
                  <input type="text" name="street" class="form-control @error('street') is-invalid @enderror"
                         placeholder="Street" value="{{ old('street', $property->street) }}">
                  @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-4">
                  <input type="text" name="district" class="form-control @error('district') is-invalid @enderror"
                         placeholder="District" value="{{ old('district', $property->district) }}">
                  @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                  <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                         placeholder="City" value="{{ old('city', $property->city) }}" required>
                  @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                  <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                         placeholder="State" value="{{ old('state', $property->state) }}" required>
                  @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="row mb-4">
                <div class="col-md-6">
                  <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                         placeholder="Country" value="{{ old('country', $property->country) }}" required>
                  @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                  <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                         placeholder="Postal Code" value="{{ old('postal_code', $property->postal_code) }}" required>
                  @error('postal_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Map Picker --}}
              <h5 class="fw-semibold mt-2 mb-3"><i class="bi bi-geo me-1"></i> Location on Map (optional)</h5>
              <div class="row mb-3">
                <div class="col-md-8">
                  <div id="map" style="height: 360px; border-radius: .75rem; overflow: hidden; border: 1px solid rgba(15,23,42,.08)"></div>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="geo-search">Search Place</label>
                  <div class="input-group mb-2">
                    <input id="geo-search" type="text" class="form-control" placeholder="Search by city, area, landmark...">
                    <button class="btn btn-outline-primary" type="button" id="geo-search-btn">
                      <i class="bi bi-search"></i>
                    </button>
                  </div>
                  <small class="text-muted d-block mb-3">
                    Tip: drag the marker to fine-tune the location.
                  </small>

                  <div class="row g-2">
                    <div class="col-6">
                      <label class="form-label small" for="latitude">Latitude</label>
                      <input id="latitude" name="latitude" type="text" class="form-control" value="{{ old('latitude', $property->latitude) }}" readonly>
                    </div>
                    <div class="col-6">
                      <label class="form-label small" for="longitude">Longitude</label>
                      <input id="longitude" name="longitude" type="text" class="form-control" value="{{ old('longitude', $property->longitude) }}" readonly>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Availability --}}
              <div class="row mb-4">
                <div class="col-md-6" data-field="available_from">
                  <label class="form-label fw-semibold" for="available_from">Available From</label>
                  <input id="available_from" type="date" name="available_from"
                         class="form-control @error('available_from') is-invalid @enderror"
                         value="{{ old('available_from', $property->available_from) }}">
                  @error('available_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6" data-field="available_to">
                  <label class="form-label fw-semibold" for="available_to">Available To</label>
                  <input id="available_to" type="date" name="available_to"
                         class="form-control @error('available_to') is-invalid @enderror"
                         value="{{ old('available_to', $property->available_to) }}">
                  @error('available_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              {{-- Amenities --}}
              <h5 class="fw-semibold mt-2 mb-3"><i class="bi bi-grid me-1"></i> Amenities</h5>
              <div class="row mb-4" data-field="amenities">
                @php $selectedAmenityIds = old('amenities', $property->amenities->pluck('id')->toArray()); @endphp
                @foreach($amenities as $amenity)
                  <div class="col-md-3 col-6 mb-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="amenities[]"
                             value="{{ $amenity->id }}" id="amenity{{ $amenity->id }}"
                             {{ in_array($amenity->id, $selectedAmenityIds, true) ? 'checked' : '' }}>
                      <label class="form-check-label" for="amenity{{ $amenity->id }}">
                        @if(!empty($amenity->icon)) <i class="fa {{ $amenity->icon }} me-1"></i>@endif
                        {{ $amenity->name }}
                      </label>
                    </div>
                  </div>
                @endforeach
              </div>

              {{-- Description --}}
              <div class="mb-4" data-field="description">
                <label class="form-label fw-semibold" for="description">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Describe your property...">{{ old('description', $property->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              {{-- Existing Images --}}
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label fw-semibold mb-0">Existing Photos</label>
                  <small class="text-muted">You can also add more photos below</small>
                </div>
                @php($media = $property->getMedia('images'))
                @if($media->count())
                  <div class="row g-3">
                    @foreach($media as $m)
                      <div class="col-6 col-md-3">
                        <div class="existing-card">
                          <img src="{{ $m->getUrl() }}" alt="photo">
                          <div class="existing-actions">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox"
                                     id="remove_media_{{ $m->id }}" name="remove_media[]"
                                     value="{{ $m->id }}">
                              <label class="form-check-label" for="remove_media_{{ $m->id }}">Remove</label>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="text-muted">No photos yet.</div>
                @endif
              </div>

              {{-- Add New Images --}}
              <div class="mb-4" data-field="images">
                <label class="form-label fw-semibold">Add New Images</label>
                <div id="dropZone" class="border border-2 border-dashed rounded-3 p-5 text-center bg-light" role="button" tabindex="0" aria-label="Upload images">
                  <i class="bi bi-cloud-arrow-up fs-1 text-primary d-block"></i>
                  <p class="mt-2 mb-0">Drag & drop images here, or <span class="text-primary fw-semibold">browse</span></p>
                  <small class="text-muted">JPEG/PNG, up to 10MB each, maximum 10 images total ({{ $media->count() }} existing)</small>
                  <input type="file" id="images" name="images[]" class="d-none" multiple accept="image/*">
                </div>
                <div id="imagePreview" class="row g-3 mt-3"></div>
                @error('images') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                @error('images.*') <div class="text-danger mt-2">{{ $message }}</div> @enderror
              </div>

              {{-- Submit --}}
              <div class="d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary px-4 py-2">
                  <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
              </div>
            </form>
          </div> {{-- /card-body --}}
        </div> {{-- /card --}}
      </div>
    </div>
  </div>
</div>
@endsection

@push('head')
  {{-- Font Awesome for amenity icons --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  {{-- Leaflet CSS --}}
  <link rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>

  <style>
    /* Page background like login/contact */
    .rb-page-bg {
      background:
        radial-gradient(1200px 600px at 90% -100px, #c7d2fe 0%, transparent 60%),
        radial-gradient(800px 500px at -200px 100%, #e9d5ff 0%, transparent 60%),
        #f8fafc;
    }
    .rb-card {
      border: 1px solid rgba(15,23,42,.06);
    }
    /* Drag & drop */
    #dropZone { cursor: pointer; transition: all .25s ease; }
    #dropZone.dragover { background: #e9f5ff; border-color: #0d6efd; }
    .preview-card { position: relative; border-radius: .5rem; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,.08); transition: transform .2s; }
    .preview-card:hover { transform: translateY(-3px); }
    .preview-card img { height: 160px; width: 100%; object-fit: cover; border-radius: .5rem; }
    .remove-btn {
      position: absolute; top: 8px; right: 8px; width: 28px; height: 28px;
      display: grid; place-items: center; border: none; border-radius: 50%;
      background: rgba(0,0,0,.6); color: #fff; cursor: pointer; line-height: 0;
    }
    .remove-btn:hover { background: rgba(220,53,69,.9); }

    /* Existing card styling */
    .existing-card { position: relative; border-radius: .75rem; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,.08); }
    .existing-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
    .existing-actions {
      position: absolute; bottom: 0; left: 0; right: 0; padding: .5rem .75rem;
      background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,.55) 100%); color: #fff;
    }
    .existing-actions .form-check-input { border-color: #fff; }
    .existing-actions label { color: #fff; }

    /* Smooth hide/show */
    .hidden-field { display: none !important; }

    /* Toast styling */
    .toast-container {
      z-index: 1055;
    }
    .toast {
      border-radius: .5rem;
      box-shadow: 0 4px 10px rgba(0,0,0,.1);
    }
    .toast .toast-body {
      font-size: 0.9rem;
    }
  </style>
@endpush

@push('scripts')
<script>
  // --- Toast Notification Function ---
  function showToast(toastId) {
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
      autohide: true,
      delay: 5000
    });
    toast.show();
  }

  // --- UTIL: normalize type name to a key (e.g., "Apartment" -> "apartment") ---
  function typeKeyFromText(txt) {
    return (txt || '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, ' ')
      .trim()
      .replace(/\s+/g, '_');
  }

  // --- Visibility map per property type name ---
  const VISIBILITY_MAP = {
    apartment: ['bedrooms', 'bathrooms', 'kitchen', 'balcony', 'hall', 'floors', 'parking', 'area', 'available_from', 'available_to', 'amenities', 'description', 'images'],
    house: ['bedrooms', 'bathrooms', 'kitchen', 'balcony', 'hall', 'floors', 'parking', 'area', 'available_from', 'available_to', 'amenities', 'description', 'images'],
    villa: ['bedrooms', 'bathrooms', 'kitchen', 'balcony', 'hall', 'floors', 'parking', 'area', 'available_from', 'available_to', 'amenities', 'description', 'images'],
    room: ['bedrooms', 'bathrooms', 'parking', 'area', 'available_from', 'available_to', 'amenities', 'description', 'images'],
    tent: ['area', 'available_from', 'available_to', 'amenities', 'description', 'images'],
    commercial: ['bathrooms', 'floors', 'parking', 'area', 'available_from', 'available_to', 'amenities', 'description', 'images'],
    _default: ['bedrooms', 'bathrooms', 'kitchen', 'balcony', 'hall', 'floors', 'parking', 'area', 'available_from', 'available_to', 'amenities', 'description', 'images']
  };

  // --- Elements registry: any wrapper with [data-field] is toggle-able ---
  function getFieldWrappers() {
    return Array.from(document.querySelectorAll('[data-field]'));
  }

  function clearAndDisableInputs(wrapper) {
    const inputs = wrapper.querySelectorAll('input, select, textarea');
    inputs.forEach(el => {
      if (el.type === 'checkbox' || el.type === 'radio') {
        el.checked = false;
      } else if (el.tagName === 'SELECT') {
        el.selectedIndex = -1;
      } else if (el.type === 'file') {
        el.value = '';
      } else {
        el.value = '';
      }
      el.setAttribute('disabled', 'disabled');
      el.setAttribute('aria-disabled', 'true');
      el.removeAttribute('required');
    });
  }

  function enableInputs(wrapper) {
    const inputs = wrapper.querySelectorAll('input, select, textarea');
    inputs.forEach(el => {
      el.removeAttribute('disabled');
      el.removeAttribute('aria-disabled');
    });
  }

  function applyVisibility() {
    const select = document.getElementById('property_type_id');
    const selectedText = select.options[select.selectedIndex]?.text || '';
    const key = typeKeyFromText(selectedText);

    const visibleSet = new Set(VISIBILITY_MAP[key] || VISIBILITY_MAP._default);

    getFieldWrappers().forEach(wrapper => {
      const fieldKey = wrapper.getAttribute('data-field');
      if (visibleSet.has(fieldKey)) {
        wrapper.classList.remove('hidden-field');
        enableInputs(wrapper);
      } else {
        wrapper.classList.add('hidden-field');
        clearAndDisableInputs(wrapper);
      }
    });
  }

  // --- INIT: apply once at load, then on change ---
  document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('property_type_id');
    setTimeout(applyVisibility, 0);
    typeSelect.addEventListener('change', applyVisibility);
  });

  // --- Dropzone ---
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('images');
  const preview = document.getElementById('imagePreview');
  let filesArray = [];
  const MAX_IMAGES = 10;
  const existingImages = {{ $media->count() }}; // Number of existing images

  // Open file picker
  const openPicker = () => fileInput.click();
  dropZone.addEventListener('click', openPicker);
  dropZone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPicker(); } });

  // Drag & drop
  dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
  dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
  dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
  });

  // File input change
  fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

  function handleFiles(selectedFiles) {
    const newFiles = Array.from(selectedFiles);
    const totalImages = existingImages + filesArray.length + newFiles.length;

    if (totalImages > MAX_IMAGES) {
      showToast('imageLimitToast');
      return;
    }

    newFiles.forEach(file => {
      filesArray.push(file);
      previewFile(file);
    });
    syncInputFiles();
  }

  function syncInputFiles() {
    const dataTransfer = new DataTransfer();
    filesArray.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
  }

  function previewFile(file) {
    const reader = new FileReader();
    reader.onload = e => {
      const col = document.createElement('div');
      col.classList.add('col-md-3', 'col-sm-6');
      col.innerHTML = `
        <div class="preview-card">
          <img src="${e.target.result}" alt="preview">
          <button type="button" class="remove-btn" aria-label="Remove image">&times;</button>
        </div>
      `;
      col.querySelector('.remove-btn').addEventListener('click', () => {
        col.remove();
        filesArray = filesArray.filter(f => f !== file);
        syncInputFiles();
      });
      preview.appendChild(col);
    };
    reader.readAsDataURL(file);
  }

  // --- Map Picker ---
  let map, marker;

  function setLatLng(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
  }

  function initMap() {
    const start = [20.5937, 78.9629];
    map = L.map('map').setView(start, 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const oldLat = parseFloat('{{ old('latitude', $property->latitude) }}');
    const oldLng = parseFloat('{{ old('longitude', $property->longitude) }}');
    if (!isNaN(oldLat) && !isNaN(oldLng)) {
      addOrMoveMarker([oldLat, oldLng], true);
    }

    map.on('click', function(e) {
      addOrMoveMarker(e.latlng, true);
    });
  }

  function addOrMoveMarker(latlng, pan) {
    if (!marker) {
      marker = L.marker(latlng, { draggable: true }).addTo(map);
      marker.on('dragend', function(ev) {
        const pos = ev.target.getLatLng();
        setLatLng(pos.lat, pos.lng);
      });
    } else {
      marker.setLatLng(latlng);
    }
    setLatLng(latlng.lat, latlng.lng);
    if (pan) map.panTo(latlng);
  }

  async function geocode(query) {
    const url = new URL('https://nominatim.openstreetmap.org/search');
    url.searchParams.set('q', query);
    url.searchParams.set('format', 'json');
    url.searchParams.set('limit', '1');

    const resp = await fetch(url, {
      headers: { 'Accept': 'application/json', 'User-Agent': 'RentApp/1.0 (Contact: admin@example.com)' }
    });
    if (!resp.ok) return null;
    const data = await resp.json();
    if (!Array.isArray(data) || !data.length) return null;
    return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon), display_name: data[0].display_name };
  }

  document.addEventListener('DOMContentLoaded', () => {
    initMap();

    const searchInput = document.getElementById('geo-search');
    const searchBtn = document.getElementById('geo-search-btn');

    async function doSearch() {
      const q = (searchInput.value || '').trim();
      if (!q) return;
      searchBtn.disabled = true;
      searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Find';
      try {
        const res = await geocode(q);
        if (res) {
          const latlng = { lat: res.lat, lng: res.lon };
          addOrMoveMarker(latlng, true);
          map.setView(latlng, 14);
        } else {
          showToast('locationNotFoundToast');
        }
      } catch(e) {
        console.error(e);
        showToast('searchErrorToast');
      } finally {
        searchBtn.disabled = false;
        searchBtn.innerHTML = '<i class="bi bi-search"></i>';
      }
    }

    searchBtn.addEventListener('click', doSearch);
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); doSearch(); }
    });
  });
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
@endpush