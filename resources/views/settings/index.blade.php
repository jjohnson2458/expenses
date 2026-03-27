@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Profile --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-person me-2"></i> Profile
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/settings') }}">
                    @csrf
                    <input type="hidden" name="section" value="profile">

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                               value="{{ old('name', Auth::user()->name ?? '') }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                               value="{{ old('email', Auth::user()->email ?? '') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>

        {{-- Password --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-lock me-2"></i> Change Password
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/settings') }}">
                    @csrf
                    <input type="hidden" name="section" value="password">

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password">
                        @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Preferences --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-sliders me-2"></i> Preferences
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/settings') }}">
                    @csrf
                    <input type="hidden" name="section" value="preferences">

                    <div class="mb-3">
                        <label for="language" class="form-label fw-semibold">Language</label>
                        <select class="form-select" id="language" name="language" style="max-width: 300px;">
                            <option value="en" {{ session('lang', 'en') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ session('lang', 'en') === 'es' ? 'selected' : '' }}>Espa&ntilde;ol</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
