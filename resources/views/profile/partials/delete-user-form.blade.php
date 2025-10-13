<section class="mt-4">
  <header class="mb-3">
    <h5 class="mb-1 text-danger">Delete Account</h5>
    <div class="text-muted">
      Once your account is deleted, all of its resources and data will be permanently deleted.
      Before deleting your account, please download any data or information that you wish to retain.
    </div>
  </header>

  <!-- Trigger button -->
  <button type="button" class="btn btn-outline-danger"
          data-bs-toggle="modal" data-bs-target="#confirmUserDeletion">
    Delete Account
  </button>

  <!-- Modal -->
  <div class="modal fade" id="confirmUserDeletion" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
        @csrf
        @method('delete')

        <div class="modal-header">
          <h5 class="modal-title" id="confirmUserDeletionLabel">Are you sure you want to delete your account?</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="text-muted">
            Once your account is deleted, all of its resources and data will be permanently deleted.
            Please enter your password to confirm you would like to permanently delete your account.
          </p>

          <div class="mt-3">
            <label for="delete_account_password" class="form-label">Password</label>
            <input id="delete_account_password" name="password" type="password"
                   class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                   placeholder="Password" autocomplete="current-password">
            @if($errors->userDeletion->has('password'))
              <div class="invalid-feedback">
                {{ $errors->userDeletion->first('password') }}
              </div>
            @endif
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete Account</button>
        </div>
      </form>
    </div>
  </div>

  {{-- If validation failed in the deletion bag, auto-open the modal --}}
  @if ($errors->userDeletion->isNotEmpty())
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('confirmUserDeletion');
        if (modalEl) {
          const modal = new bootstrap.Modal(modalEl);
          modal.show();
        }
      });
    </script>
  @endif
</section>
