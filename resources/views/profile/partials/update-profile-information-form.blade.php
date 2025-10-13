<section>
  <header class="mb-3">
    <h5 class="mb-1">Profile Information</h5>
    <div class="text-muted">Update your account's profile information and email address.</div>
  </header>

  {{-- Hidden form to re-send verification link --}}
  <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="d-none">
    @csrf
  </form>

  <form method="POST" action="{{ route('profile.update') }}" class="vstack gap-3">
    @csrf
    @method('patch')

    {{-- Name --}}
    <div>
      <label for="name" class="form-label">Name</label>
      <input id="name" name="name" type="text"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Email --}}
    <div>
      <label for="email" class="form-label">Email</label>
      <input id="email" name="email" type="email"
             class="form-control @error('email') is-invalid @enderror"
             value="{{ old('email', $user->email) }}" required autocomplete="username">
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror

      @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-2 small">
          <span class="text-muted">Your email address is unverified.</span>
          <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline">
            Click here to re-send the verification email.
          </button>

          @if (session('status') === 'verification-link-sent')
            <div class="text-success mt-1">A new verification link has been sent to your email address.</div>
          @endif
        </div>
      @endif
    </div>

    {{-- Phone number (new) --}}
    <div>
      <label for="phone_number" class="form-label">Phone number</label>
      <input id="phone_number" name="phone_number" type="tel"
             class="form-control @error('phone_number') is-invalid @enderror"
             value="{{ old('phone_number', $user->phone_number) }}"
             required autocomplete="tel" >
      @error('phone_number')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="d-flex align-items-center gap-3">
      <button type="submit" class="btn btn-primary">Save</button>

      @if (session('status') === 'profile-updated')
        <span class="text-success small">Saved.</span>
      @endif
    </div>
  </form>
</section>
