@extends('layouts.app')

@section('title', isset($category) ? 'Edit Category' : 'Add Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-{{ isset($category) ? 'pencil' : 'plus-circle' }} me-2"></i>
                    {{ isset($category) ? 'Edit Category' : 'New Category' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($category) ? url('/categories/' . $category['id']) : url('/categories') }}">
                    @csrf

                    {{-- Name (EN) --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Name (English) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                               value="{{ old('name', $category['name'] ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Name (ES) --}}
                    <div class="mb-3">
                        <label for="name_es" class="form-label fw-semibold">Name (Spanish)</label>
                        <input type="text" class="form-control @error('name_es') is-invalid @enderror" id="name_es" name="name_es"
                               value="{{ old('name_es', $category['name_es'] ?? '') }}">
                        @error('name_es') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                               value="{{ old('description', $category['description'] ?? '') }}">
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        {{-- Color --}}
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label fw-semibold">Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" id="color" name="color"
                                       value="{{ old('color', $category['color'] ?? '#4e73df') }}">
                                <input type="text" class="form-control" id="colorText" value="{{ old('color', $category['color'] ?? '#4e73df') }}" maxlength="7" style="max-width: 100px;">
                            </div>
                            @error('color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        {{-- Icon --}}
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label fw-semibold">Bootstrap Icon</label>
                            <div class="input-group">
                                <span class="input-group-text" id="iconPreview"><i class="bi bi-{{ old('icon', $category['icon'] ?? 'tag') }}"></i></span>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon"
                                       value="{{ old('icon', $category['icon'] ?? '') }}" placeholder="e.g. cart, house, car-front">
                            </div>
                            <small class="text-muted">See <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> for names</small>
                            @error('icon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Sort Order --}}
                    <div class="mb-3">
                        <label for="sort_order" class="form-label fw-semibold">Sort Order</label>
                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order"
                               value="{{ old('sort_order', $category['sort_order'] ?? 0) }}" min="0" style="max-width: 120px;">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Active --}}
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                {{ old('active', $category['active'] ?? 1) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="active">Active</label>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ isset($category) ? 'Update Category' : 'Save Category' }}
                        </button>
                        <a href="{{ url('/categories') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    // Sync color picker and text input
    $('#color').on('input', function() { $('#colorText').val(this.value); });
    $('#colorText').on('input', function() { $('#color').val(this.value); });

    // Icon preview
    $('#icon').on('input', function() {
        $('#iconPreview i').attr('class', 'bi bi-' + (this.value || 'tag'));
    });
});
</script>
@endpush
