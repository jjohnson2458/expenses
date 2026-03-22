<?php
/**
 * Report Controller - Expense Report management
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\ExpenseReport;
use App\Models\Expense;
use App\Models\Category;

class ReportController extends Controller
{
    /**
     * List all expense reports with totals
     */
    public function index(): void
    {
        $this->requireAuth();

        $reportModel = new ExpenseReport();
        $reports = $reportModel->getWithTotals();

        $this->view('expenses.reports.index', [
            'reports' => $reports,
        ]);
    }

    /**
     * Show the create report form
     */
    public function create(): void
    {
        $this->requireAuth();

        $this->view('expenses.reports.form', [
            'report' => null,
        ]);
    }

    /**
     * Store a new report
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setFlash('danger', 'Report title is required.');
            $this->redirect('/reports/create');
            return;
        }

        $reportModel = new ExpenseReport();
        $reportModel->create([
            'user_id'      => $_SESSION['user_id'],
            'title'        => $title,
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => $_POST['status'] ?? 'draft',
            'date_from'    => $_POST['date_from'] ?: null,
            'date_to'      => $_POST['date_to'] ?: null,
            'total_amount' => 0,
        ]);

        $this->setFlash('success', 'Report created successfully.');
        $this->redirect('/reports');
    }

    /**
     * Show a single report with linked and available expenses
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $reportModel = new ExpenseReport();
        $report = $reportModel->find($id);
        if (!$report) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $expenseModel = new Expense();
        $categoryModel = new Category();

        // Get expenses linked to this report
        $linkedExpenses = $expenseModel->getByReport($id);

        // Get available expenses (not linked to any report)
        $availableExpenses = $expenseModel->where(['report_id' => null], 'expense_date', 'DESC');

        // Since Model::where uses = comparison, we need a custom query for NULL
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare("SELECT e.*, c.name AS category_name FROM expenses e LEFT JOIN expense_categories c ON e.category_id = c.id WHERE e.report_id IS NULL ORDER BY e.expense_date DESC");
        $stmt->execute();
        $availableExpenses = $stmt->fetchAll();

        // Get categories for display
        $categories = $categoryModel->getActive();
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = $cat['name'];
        }

        $this->view('expenses.reports.show', [
            'report'            => $report,
            'linkedExpenses'    => $linkedExpenses,
            'availableExpenses' => $availableExpenses,
            'categoryMap'       => $categoryMap,
        ]);
    }

    /**
     * Show the edit report form
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $reportModel = new ExpenseReport();
        $report = $reportModel->find($id);
        if (!$report) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $this->view('expenses.reports.form', [
            'report' => $report,
        ]);
    }

    /**
     * Update an existing report
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setFlash('danger', 'Report title is required.');
            $this->redirect("/reports/{$id}/edit");
            return;
        }

        $reportModel = new ExpenseReport();
        $report = $reportModel->find($id);
        if (!$report) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $reportModel->update($id, [
            'title'       => $title,
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'draft',
            'date_from'   => $_POST['date_from'] ?: null,
            'date_to'     => $_POST['date_to'] ?: null,
        ]);

        $this->setFlash('success', 'Report updated successfully.');
        $this->redirect("/reports/{$id}");
    }

    /**
     * Delete a report and unlink all expenses
     */
    public function destroy(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $reportModel = new ExpenseReport();
        $report = $reportModel->find($id);
        if (!$report) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        // Unlink all expenses from this report
        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare("UPDATE expenses SET report_id = NULL WHERE report_id = :id");
        $stmt->execute(['id' => $id]);

        // Delete the report
        $reportModel->delete($id);

        $this->setFlash('success', 'Report deleted successfully.');
        $this->redirect('/reports');
    }

    /**
     * Add an expense to a report
     */
    public function addExpense(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $expenseId = (int) ($_POST['expense_id'] ?? 0);
        if ($expenseId <= 0) {
            $this->setFlash('danger', 'No expense selected.');
            $this->redirect("/reports/{$id}");
            return;
        }

        $expenseModel = new Expense();
        $expenseModel->update($expenseId, ['report_id' => $id]);

        $reportModel = new ExpenseReport();
        $reportModel->updateTotal($id);

        $this->setFlash('success', 'Expense added to report.');
        $this->redirect("/reports/{$id}");
    }

    /**
     * Remove an expense from a report
     */
    public function removeExpense(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $expenseId = (int) ($_POST['expense_id'] ?? 0);
        if ($expenseId <= 0) {
            $this->setFlash('danger', 'No expense specified.');
            $this->redirect("/reports/{$id}");
            return;
        }

        $db = \App\Helpers\Database::getInstance();
        $stmt = $db->prepare("UPDATE expenses SET report_id = NULL WHERE id = :id AND report_id = :report_id");
        $stmt->execute(['id' => $expenseId, 'report_id' => $id]);

        $reportModel = new ExpenseReport();
        $reportModel->updateTotal($id);

        $this->setFlash('success', 'Expense removed from report.');
        $this->redirect("/reports/{$id}");
    }

    /**
     * Print-friendly report view
     */
    public function printReport(int $id): void
    {
        $this->requireAuth();

        $reportModel = new ExpenseReport();
        $report = $reportModel->find($id);
        if (!$report) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $expenseModel = new Expense();
        $categoryModel = new Category();

        $linkedExpenses = $expenseModel->getByReport($id);

        $categories = $categoryModel->getActive();
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = $cat['name'];
        }

        $this->view('expenses.reports.print', [
            'report'         => $report,
            'linkedExpenses' => $linkedExpenses,
            'categoryMap'    => $categoryMap,
        ]);
    }
}
