{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.owner')

@push('styles')

@endpush

@section('owner')
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Profile</h3>
      <div class="text-muted">Manage your account settings</div>
    </div>
  </div>

  <div class="row g-3">
    {{-- Update profile information --}}
    <div class="col-12">
      <div class="p-3 profile-card">
        <div class="mx-auto" style="max-width:640px">
          @include('profile.partials.update-profile-information-form')
        </div>
      </div>
    </div>

    {{-- Update password --}}
    <div class="col-12">
      <div class="p-3 profile-card">
        <div class="mx-auto" style="max-width:640px">
          @include('profile.partials.update-password-form')
        </div>
      </div>
    </div>

    {{-- Delete account --}}
    <div class="col-12">
      <div class="p-3 profile-card">
        <div class="mx-auto" style="max-width:640px">
          @include('profile.partials.delete-user-form')
        </div>
      </div>
    </div>
  </div>
@endsection
