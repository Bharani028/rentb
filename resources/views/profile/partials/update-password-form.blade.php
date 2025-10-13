<section class="mt-4">
  <header class="mb-3">
    <h5 class="mb-1">Update Password</h5>
    <div class="text-muted">Ensure your account is using a long, random password to stay secure.</div>
  </header>

  <form method="POST" action="{{ route('password.update') }}" class="vstack gap-3">
    @csrf
    @method('put')

    <div>
      <label for="update_password_current_password" class="form-label">Current Password</label>
      <input id="update_password_current_password" name="current_password" type="password"
             class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
             autocomplete="current-password">
      @if($errors->updatePassword->has('current_password'))
        <div class="invalid-feedback">
          {{ $errors->updatePassword->first('current_password') }}
        </div>
      @endif
    </div>

    <div>
      <label for="update_password_password" class="form-label">New Password</label>
      <input id="update_password_password" name="password" type="password"
             class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
             autocomplete="new-password">
      @if($errors->updatePassword->has('password'))
        <div class="invalid-feedback">
          {{ $errors->updatePassword->first('password') }}
        </div>
      @endif
    </div>

    <div>
      <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
      <input id="update_password_password_confirmation" name="password_confirmation" type="password"
             class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
             autocomplete="new-password">
      @if($errors->updatePassword->has('password_confirmation'))
        <div class="invalid-feedback">
          {{ $errors->updatePassword->first('password_confirmation') }}
        </div>
      @endif
    </div>

    <div class="d-flex align-items-center gap-3">
      <button type="submit" class="btn btn-primary">Save</button>

      @if (session('status') === 'password-updated')
        <span class="text-success small">Saved.</span>
      @endif
    </div>
  </form>
</section>
