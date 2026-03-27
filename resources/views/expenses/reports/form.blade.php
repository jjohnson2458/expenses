@extends('layouts.app')

@section('title', isset($report) ? 'Edit Report' : 'New Report')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-{{ isset($report) ? 'pencil' : 'plus-circle' }} me-2"></i>
                    {{ isset($report) ? 'Edit Report' : 'New Report' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($report) ? url('/reports/' . $report['id']) : url('/reports') }}">
                    @csrf
                    @if(isset($report))
                        @method('PUT')
                    @endif

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                               value="{{ old('title', $report['title'] ?? '') }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $report['description'] ?? '') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="draft" {{ old('status', $report['status'] ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ old('status', $report['status'] ?? '') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="approved" {{ old('status', $report['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status', $report['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Date Range --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-semibold">Start Date</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date"
                                   value="{{ old('start_date', $report['start_date'] ?? '') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-semibold">End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date"
                                   value="{{ old('end_date', $report['end_date'] ?? '') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ isset($report) ? 'Update Report' : 'Create Report' }}
                        </button>
                        <a href="{{ url('/reports') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
