@extends('layouts.app')

@section('title', isset($recurring) ? 'Edit Recurring Expense' : 'Add Recurring Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-{{ isset($recurring) ? 'pencil' : 'plus-circle' }} me-2"></i>
                    {{ isset($recurring) ? 'Edit Recurring Expense' : 'New Recurring Expense' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($recurring) ? url('/recurring/' . $recurring['id']) : url('/recurring') }}">
                    @csrf
                    @if(isset($recurring))
                        @method('PUT')
                    @endif

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeDebit" value="debit"
                                    {{ old('type', $recurring['type'] ?? 'debit') === 'debit' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-semibold" for="typeDebit">
                                    <i class="bi bi-arrow-up-circle me-1"></i> Debit
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeCredit" value="credit"
                                    {{ old('type', $recurring['type'] ?? '') === 'credit' ? 'checked' : '' }}>
                                <label class="form-check-label text-success fw-semibold" for="typeCredit">
                                    <i class="bi bi-arrow-down-circle me-1"></i> Credit
                                </label>
                            </div>
                        </div>
                        @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                               value="{{ old('description', $recurring['description'] ?? '') }}" required>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        {{-- Amount --}}
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                                       value="{{ old('amount', $recurring['amount'] ?? '') }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Day of Month --}}
                        <div class="col-md-6 mb-3">
                            <label for="day_of_month" class="form-label fw-semibold">Day of Month <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('day_of_month') is-invalid @enderror" id="day_of_month" name="day_of_month"
                                   value="{{ old('day_of_month', $recurring['day_of_month'] ?? 1) }}" min="1" max="31" required>
                            @error('day_of_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Category --}}
                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id ?? $category['id'] }}"
                                    {{ old('category_id', $recurring['category_id'] ?? '') == ($category->id ?? $category['id']) ? 'selected' : '' }}>
                                    {{ $category->name ?? $category['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Vendor --}}
                    <div class="mb-3">
                        <label for="vendor" class="form-label fw-semibold">Vendor</label>
                        <input type="text" class="form-control @error('vendor') is-invalid @enderror" id="vendor" name="vendor"
                               value="{{ old('vendor', $recurring['vendor'] ?? '') }}">
                        @error('vendor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Active --}}
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                {{ old('active', $recurring['active'] ?? 1) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="active">Active</label>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ isset($recurring) ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ url('/recurring') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
