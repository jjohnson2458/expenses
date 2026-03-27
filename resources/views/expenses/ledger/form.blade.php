@extends('layouts.app')

@section('title', isset($expense) ? 'Edit Expense' : 'Add Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-{{ isset($expense) ? 'pencil' : 'plus-circle' }} me-2"></i>
                    {{ isset($expense) ? 'Edit Expense' : 'New Expense' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($expense) ? url('/expenses/' . $expense['id']) : url('/expenses') }}" enctype="multipart/form-data">
                    @csrf
                    @if(isset($expense))
                        @method('PUT')
                    @endif

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeDebit" value="debit"
                                    {{ old('type', $expense['type'] ?? 'debit') === 'debit' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-semibold" for="typeDebit">
                                    <i class="bi bi-arrow-up-circle me-1"></i> Debit
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeCredit" value="credit"
                                    {{ old('type', $expense['type'] ?? '') === 'credit' ? 'checked' : '' }}>
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
                               value="{{ old('description', $expense['description'] ?? '') }}" required>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        {{-- Amount --}}
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                                       value="{{ old('amount', $expense['amount'] ?? '') }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date"
                                   value="{{ old('date', $expense['date'] ?? date('Y-m-d')) }}" required>
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        {{-- Category --}}
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label fw-semibold">Category</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id ?? $category['id'] }}"
                                        {{ old('category_id', $expense['category_id'] ?? '') == ($category->id ?? $category['id']) ? 'selected' : '' }}>
                                        {{ $category->name ?? $category['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Vendor --}}
                        <div class="col-md-6 mb-3">
                            <label for="vendor" class="form-label fw-semibold">Vendor</label>
                            <input type="text" class="form-control @error('vendor') is-invalid @enderror" id="vendor" name="vendor"
                                   value="{{ old('vendor', $expense['vendor'] ?? '') }}">
                            @error('vendor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Report --}}
                    <div class="mb-3">
                        <label for="report_id" class="form-label fw-semibold">Report</label>
                        <select class="form-select @error('report_id') is-invalid @enderror" id="report_id" name="report_id">
                            <option value="">No Report</option>
                            @foreach($reports ?? [] as $report)
                                <option value="{{ $report->id ?? $report['id'] }}"
                                    {{ old('report_id', $expense['report_id'] ?? '') == ($report->id ?? $report['id']) ? 'selected' : '' }}>
                                    {{ $report->title ?? $report['title'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('report_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $expense['notes'] ?? '') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Receipt --}}
                    <div class="mb-4">
                        <label for="receipt" class="form-label fw-semibold">Receipt</label>
                        <input type="file" class="form-control @error('receipt') is-invalid @enderror" id="receipt" name="receipt" accept="image/*,.pdf">
                        @error('receipt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if(!empty($expense['receipt_path'] ?? ''))
                            <div class="mt-2 small text-muted">
                                <i class="bi bi-paperclip me-1"></i> Current: {{ basename($expense['receipt_path']) }}
                            </div>
                        @endif
                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> {{ isset($expense) ? 'Update Expense' : 'Save Expense' }}
                        </button>
                        <a href="{{ url('/expenses') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
