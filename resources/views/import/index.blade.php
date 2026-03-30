@extends('layouts.app')

@section('title', 'Import Expenses')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Upload Form --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-upload me-2"></i> Import Expenses
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/import') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="import_file" class="form-label fw-semibold">Select File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('import_file') is-invalid @enderror" id="import_file" name="import_file" accept=".csv,.ofx,.qfx,.qbo,.txt" required>
                        @error('import_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">
                            Accepted formats: <strong>CSV</strong>, <strong>OFX</strong> (Open Financial Exchange), <strong>QFX</strong> (Quicken), <strong>QBO</strong> (QuickBooks). Max 5MB.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Default Category (optional)</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">None / Auto-detect from CSV</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </form>
            </div>
        </div>

        {{-- Bank File Instructions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-bank me-2"></i> Bank Export Files (OFX / QFX / QBO)
                </h5>
            </div>
            <div class="card-body">
                <p>Download transaction files directly from your bank's website and import them here. Most banks offer one or more of these formats:</p>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold"><i class="bi bi-file-earmark-code me-1"></i> .OFX</h6>
                            <p class="small text-muted mb-0">Open Financial Exchange. Universal bank export format supported by most financial institutions.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold"><i class="bi bi-file-earmark-code me-1"></i> .QFX</h6>
                            <p class="small text-muted mb-0">Quicken Web Connect. Used by Quicken &mdash; same OFX format with Intuit headers.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-semibold"><i class="bi bi-file-earmark-code me-1"></i> .QBO</h6>
                            <p class="small text-muted mb-0">QuickBooks Web Connect. Used by QuickBooks &mdash; same OFX format with QB headers.</p>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-shield-check me-1"></i>
                    <strong>Duplicate protection:</strong> Each bank transaction has a unique ID (FITID). Re-importing the same file will skip transactions that were already imported.
                </div>
            </div>
        </div>

        {{-- CSV Instructions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-filetype-csv me-2"></i> CSV Format Instructions
                </h5>
            </div>
            <div class="card-body">
                <p>Your CSV file should include the following columns:</p>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Description</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>date</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Date of expense (YYYY-MM-DD or MM/DD/YYYY)</td>
                                <td>2026-03-15</td>
                            </tr>
                            <tr>
                                <td><code>description</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Description of the expense</td>
                                <td>Office Supplies</td>
                            </tr>
                            <tr>
                                <td><code>amount</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Amount (negative values auto-detected as debits)</td>
                                <td>49.99</td>
                            </tr>
                            <tr>
                                <td><code>type</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>"debit" or "credit" (default: debit)</td>
                                <td>debit</td>
                            </tr>
                            <tr>
                                <td><code>category</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Category name (matched by name)</td>
                                <td>Office</td>
                            </tr>
                            <tr>
                                <td><code>vendor</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Vendor or payee name</td>
                                <td>Staples</td>
                            </tr>
                            <tr>
                                <td><code>notes</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Additional notes</td>
                                <td>Monthly supplies order</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-semibold">Sample CSV</h6>
                <div class="bg-light rounded p-3">
                    <code class="small">
                        date,description,amount,type,category,vendor,notes<br>
                        2026-03-01,Internet Service,89.99,debit,Utilities,Comcast,Monthly internet<br>
                        2026-03-05,Client Payment,1500.00,credit,Income,Acme Corp,March invoice<br>
                        2026-03-10,Office Supplies,45.50,debit,Office,Amazon,Printer paper
                    </code>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Tip:</strong> The first row must be the header row. Column names are case-insensitive and can use spaces or underscores.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
