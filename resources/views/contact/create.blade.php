{{-- resources/views/contact/create.blade.php --}}
@extends('layouts.app')

@push('head')
<style>
  :root { --rb-primary:#4f46e5; }
  .contact-bg{
    background:
      radial-gradient(1200px 600px at 90% -100px, #c7d2fe 0%, transparent 60%),
      radial-gradient(800px 500px at -200px 100%, #e9d5ff 0%, transparent 60%),
      #f8fafc;
  }
  .contact-card{
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(2,6,23,.08), 0 2px 6px rgba(2,6,23,.06);
    border: 1px solid rgba(15,23,42,.06);
    background:#fff;
  }
  .form-control, .input-group-text, .btn { border-radius: 12px; }
  .btn-primary{ --bs-btn-bg: var(--rb-primary); --bs-btn-border-color: var(--rb-primary); }
  .btn-primary:hover{ filter:brightness(.95); }
</style>
@endpush

@section('content')
<div class="contact-bg">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="contact-card">
          <div class="p-4 p-md-5">
            <h3 class="mb-1">Contact Us</h3>
            <p class="text-muted mb-4">Have a question about {{ config('app.name', 'RentB') }}? Send us a note and we’ll get back to you.</p>

            <form method="POST" action="{{ route('contact.send') }}" novalidate>
              @csrf

              {{-- Honeypot (hidden) --}}
              <input type="text" name="website" tabindex="-1" autocomplete="off"
                     style="position:absolute;left:-9999px;opacity:0;">

              <div class="row g-3">
                {{-- Name --}}
                <div class="col-md-6">
                  <label class="form-label" for="name">Your name</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                  </div>
                  @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                  <label class="form-label" for="email">Email</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                  </div>
                  @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Subject --}}
                <div class="col-12">
                  <label class="form-label" for="subject">Subject</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-chat-text"></i></span>
                    <input type="text" id="subject" name="subject"
                           value="{{ old('subject') }}"
                           class="form-control @error('subject') is-invalid @enderror" required>
                  </div>
                  @error('subject') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Message --}}
                <div class="col-12">
                  <label class="form-label" for="message">Message</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-pencil-square"></i></span>
                    <textarea id="message" name="message" rows="5"
                              class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                  </div>
                  @error('message') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"
                        onclick="this.disabled=true; this.form.submit();">
                  <i class="bi bi-send me-1"></i> Send message
                </button>
                <a href="{{ url('/') }}" class="btn btn-light">Cancel</a>
              </div>

              <div class="text-muted small mt-3">
                We’ll only use your email to reply to this request.
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
