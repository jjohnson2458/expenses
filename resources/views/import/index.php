<?php
/**
 * Import Expenses — Upload CSV file
 */

ob_start();
$title = 'Import Expenses';
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <!-- Upload Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-upload me-2"></i>Import Expenses from CSV
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/import" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="import_file" class="form-label fw-semibold">Select File</label>
                        <input type="file"
                               class="form-control"
                               id="import_file"
                               name="import_file"
                               accept=".csv,.xlsx"
                               required>
                        <div class="form-text">Accepted formats: CSV (.csv) or Excel (.xlsx)</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/export/csv?template=1" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download me-1"></i>Download CSV Template
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>Expected CSV Format
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Your CSV file should contain a header row with the following columns. <strong>Date</strong>, <strong>Description</strong>, and <strong>Amount</strong> are required; the rest are optional.</p>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Column</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>Date</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Expense date (YYYY-MM-DD, MM/DD/YYYY, or M/D/YY)</td>
                            </tr>
                            <tr>
                                <td><code>Description</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Short description of the expense</td>
                            </tr>
                            <tr>
                                <td><code>Amount</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Dollar amount (e.g. 49.99 or $1,250.00)</td>
                            </tr>
                            <tr>
                                <td><code>Category</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Category name — must match an existing category exactly</td>
                            </tr>
                            <tr>
                                <td><code>Type</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td><code>debit</code> or <code>credit</code> (defaults to debit)</td>
                            </tr>
                            <tr>
                                <td><code>Vendor</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Vendor or merchant name</td>
                            </tr>
                            <tr>
                                <td><code>Notes</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Additional notes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="mt-4 mb-2">Sample CSV</h6>
                <div class="bg-light p-3 rounded border">
                    <pre class="mb-0" style="font-size: 0.85rem; white-space: pre-wrap;">Date,Description,Category,Amount,Type,Vendor,Notes
2026-03-15,Office Supplies,Office,49.99,debit,Staples,Printer paper and ink
2026-03-16,Client Lunch,Meals,32.50,debit,Olive Garden,Meeting with ABC Corp
2026-03-18,Invoice Payment,Income,1500.00,credit,ABC Corp,March consulting fee
2026-03-20,Gas,Transportation,45.00,debit,Shell,</pre>
                </div>
            </div>
        </div>

        <!-- Warning Note -->
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Note on duplicates:</strong> The import process does not check for duplicate entries. If you import the same file twice, duplicate expense records will be created. Please review your data before importing.
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();
require VIEW_PATH . '/layouts/app.php';
?>
