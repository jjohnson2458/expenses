@extends('layouts.app')

@section('title', isset($expense) ? 'Edit Expense' : 'Add Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        @if(!isset($expense))
        {{-- Quick Scan Card --}}
        <div class="card border-0 shadow-sm mb-3" id="scanCard">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;background:rgba(78,115,223,0.1)">
                        <i class="bi bi-camera text-primary" style="font-size:1.2rem"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Scan a Receipt</h6>
                        <small class="text-muted">Upload a photo and AI will extract the details</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <label for="scanInput" class="btn btn-primary mb-0" style="cursor:pointer">
                        <i class="bi bi-upload me-1"></i>Upload Receipt
                    </label>
                    <input type="file" id="scanInput" accept="image/*" class="d-none" capture="environment">
                    <button type="button" id="scanCamera" class="btn btn-outline-primary d-md-none">
                        <i class="bi bi-camera me-1"></i>Camera
                    </button>
                </div>
                <div id="scanStatus" class="mt-3 d-none">
                    <div class="d-flex align-items-center text-primary">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span>Processing receipt...</span>
                    </div>
                </div>
                <div id="scanError" class="mt-3 d-none">
                    <div class="alert alert-warning mb-0 py-2 small"></div>
                </div>
            </div>
        </div>
        @endif

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
                        <div class="form-text">JPG, PNG, GIF, WebP, or PDF up to 10MB</div>
                        @error('receipt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if(!empty($expense['receipt_path'] ?? ($expense->receipt_path ?? '')))
                            @php $receiptPath = $expense['receipt_path'] ?? $expense->receipt_path; @endphp
                            <div class="mt-2 p-3 bg-light rounded">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="small text-muted">
                                        <i class="bi bi-paperclip me-1"></i> {{ basename($receiptPath) }}
                                    </span>
                                    <a href="{{ asset('storage/' . $receiptPath) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </div>
                                @if(in_array(strtolower(pathinfo($receiptPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ asset('storage/' . $receiptPath) }}" alt="Receipt" class="mt-2 img-fluid rounded" style="max-height: 200px;">
                                @endif
                            </div>
                        @endif
                        {{-- Preview for new uploads --}}
                        <div id="receiptPreview" class="mt-2 d-none">
                            <img id="receiptPreviewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
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

@push('scripts')
<script>
// Receipt scan handler
document.getElementById('scanInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('receipt', file);

    document.getElementById('scanStatus').classList.remove('d-none');
    document.getElementById('scanError').classList.add('d-none');

    $.ajax({
        url: '/expenses/scan',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(res) {
            document.getElementById('scanStatus').classList.add('d-none');

            if (res.success && res.data) {
                // Auto-fill form fields
                if (res.data.description) document.getElementById('description').value = res.data.description;
                if (res.data.amount) document.getElementById('amount').value = res.data.amount;
                if (res.data.date) document.getElementById('date').value = res.data.date;
                if (res.data.vendor) document.getElementById('vendor').value = res.data.vendor;
                if (res.data.category_id) document.getElementById('category_id').value = res.data.category_id;

                // Flash success on the scan card
                document.getElementById('scanCard').style.borderLeft = '4px solid #1cc88a';

                // Show receipt preview
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('receiptPreviewImg').src = ev.target.result;
                    document.getElementById('receiptPreview').classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                const errEl = document.getElementById('scanError');
                errEl.classList.remove('d-none');
                errEl.querySelector('.alert').textContent = res.message || 'Could not extract receipt data. Please fill in manually.';
            }
        },
        error: function() {
            document.getElementById('scanStatus').classList.add('d-none');
            const errEl = document.getElementById('scanError');
            errEl.classList.remove('d-none');
            errEl.querySelector('.alert').textContent = 'Error processing receipt. Please try again.';
        }
    });
});

// Camera button triggers the same input with capture
document.getElementById('scanCamera')?.addEventListener('click', function() {
    document.getElementById('scanInput').click();
});

document.getElementById('receipt')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('receiptPreview');
    const img = document.getElementById('receiptPreviewImg');
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            img.src = ev.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('d-none');
    }
});
</script>
@endpush
