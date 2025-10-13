@extends('layouts.admin')

@section('page_title', 'Create User')

@push('styles')
  <style>
    .form-label { font-weight: 500; }
    .form-control.is-invalid { border-color: #dc3545; }
    .invalid-feedback { display: block; }
  </style>
@endpush

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Create User</h3>
      <div class="subtle">Add a new user to the system</div>
    </div>
    <div>
      <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
      </a>
    </div>
  </div>

  <div class="card modern-card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label for="name" class="form-label">Name</label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label for="role" class="form-label">Role</label>
          <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
            <option value="">Select Role</option>
            @foreach($roles as $role)
              <option value="{{ $role }}" @selected(old('role') == $role)>{{ ucfirst($role) }}</option>
            @endforeach
          </select>
          @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection