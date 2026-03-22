<?php
/**
 * Recurring Expense Controller — manage recurring expense templates
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\RecurringExpense;
use App\Models\Category;
use App\Models\Expense;
use PDO;

class RecurringExpenseController extends Controller
{
    /**
     * List all recurring expenses with category names
     */
    public function index(): void
    {
        $this->requireAuth();

        $model = new RecurringExpense();
        $db = $model->getDb();

        $sql = "SELECT r.*, c.name AS category_name, c.color AS category_color
                FROM recurring_expenses r
                LEFT JOIN expense_categories c ON r.category_id = c.id
                ORDER BY r.is_active DESC, r.id DESC";

        $stmt = $db->query($sql);
        $recurring = $stmt->fetchAll();

        $this->view('expenses.recurring.index', [
            'recurring' => $recurring,
        ]);
    }

    /**
     * Show the create form
     */
    public function create(): void
    {
        $this->requireAuth();

        $categoryModel = new Category();
        $categories = $categoryModel->getActive();

        $this->view('expenses.recurring.form', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new recurring expense
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $errors = $this->validateInput();
        if (!empty($errors)) {
            $this->setFlash('danger', implode('<br>', $errors));
            $this->redirect('/recurring/create');
        }

        $model = new RecurringExpense();
        $model->create($this->buildData());

        $this->setFlash('success', 'Recurring expense created successfully.');
        $this->redirect('/recurring');
    }

    /**
     * Show the edit form
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $model = new RecurringExpense();
        $recurring = $model->find($id);
        if (!$recurring) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getActive();

        $this->view('expenses.recurring.form', [
            'recurring'  => $recurring,
            'categories' => $categories,
        ]);
    }

    /**
     * Update an existing recurring expense
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $model = new RecurringExpense();
        $recurring = $model->find($id);
        if (!$recurring) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $errors = $this->validateInput();
        if (!empty($errors)) {
            $this->setFlash('danger', implode('<br>', $errors));
            $this->redirect("/recurring/{$id}/edit");
        }

        $model->update($id, $this->buildData());

        $this->setFlash('success', 'Recurring expense updated successfully.');
        $this->redirect('/recurring');
    }

    /**
     * Delete a recurring expense
     */
    public function destroy(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $model = new RecurringExpense();
        $recurring = $model->find($id);
        if (!$recurring) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $model->delete($id);

        $this->setFlash('success', 'Recurring expense deleted successfully.');
        $this->redirect('/recurring');
    }

    /**
     * Process all due recurring expenses for the current month
     */
    public function processMonthly(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $recurringModel = new RecurringExpense();
        $expenseModel   = new Expense();
        $today          = date('Y-m-d');

        $due = $recurringModel->getDueForProcessing($today);
        $count = 0;

        foreach ($due as $item) {
            $expenseModel->create([
                'description'  => $item['description'],
                'amount'       => $item['amount'],
                'type'         => $item['type'] ?? 'debit',
                'expense_date' => $today,
                'category_id'  => $item['category_id'],
                'vendor'       => $item['vendor'] ?? null,
                'user_id'      => $_SESSION['user_id'],
                'is_recurring' => 1,
            ]);

            $recurringModel->markProcessed((int) $item['id'], $today);
            $count++;
        }

        $this->setFlash('success', "{$count} recurring expense(s) processed successfully.");
        $this->redirect('/recurring');
    }

    /**
     * Validate form input
     */
    private function validateInput(): array
    {
        $errors = [];

        if (empty(trim($_POST['description'] ?? ''))) {
            $errors[] = 'Description is required.';
        }

        if (!isset($_POST['amount']) || $_POST['amount'] === '' || !is_numeric($_POST['amount'])) {
            $errors[] = 'A valid amount is required.';
        }

        $day = (int) ($_POST['day_of_month'] ?? 0);
        if ($day < 1 || $day > 31) {
            $errors[] = 'Day of month must be between 1 and 31.';
        }

        return $errors;
    }

    /**
     * Build data array from POST input
     */
    private function buildData(): array
    {
        $data = [
            'description'  => trim($_POST['description']),
            'amount'       => (float) $_POST['amount'],
            'type'         => in_array($_POST['type'] ?? 'debit', ['debit', 'credit']) ? $_POST['type'] : 'debit',
            'day_of_month' => (int) $_POST['day_of_month'],
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
            'user_id'      => $_SESSION['user_id'],
        ];

        $data['category_id'] = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $data['vendor']      = !empty($_POST['vendor']) ? trim($_POST['vendor']) : null;

        return $data;
    }
}
