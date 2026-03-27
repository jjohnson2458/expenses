@extends('layouts.app')

@section('title', 'Import Expenses')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Upload Form --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-upload me-2"></i> Import Expenses from CSV
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('/import') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="csv_file" class="form-label fw-semibold">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv" required>
                        @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Only .csv files are accepted. Maximum 2MB.</div>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Default Category (optional)</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Auto-detect from CSV</option>
                            @foreach($categories ?? [] as $category)
                                <option value="{{ $category->id ?? $category['id'] }}">{{ $category->name ?? $category['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </form>
            </div>
        </div>

        {{-- Instructions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2"></i> CSV Format Instructions
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
                                <td>Date of expense (YYYY-MM-DD)</td>
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
                                <td>Amount (positive number)</td>
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
