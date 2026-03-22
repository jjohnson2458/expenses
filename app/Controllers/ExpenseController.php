<?php
/**
 * Expense Controller — CRUD for the expense ledger
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\ExpenseReport;
use PDO;

class ExpenseController extends Controller
{
    /**
     * List expenses with search, date range, and category filters
     */
    public function index(): void
    {
        $this->requireAuth();

        $expenseModel  = new Expense();
        $categoryModel = new Category();

        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 20;
        $q        = trim($_GET['q'] ?? '');
        $from     = $_GET['from'] ?? '';
        $to       = $_GET['to'] ?? '';
        $category = $_GET['category'] ?? '';

        $categories = $categoryModel->getActive();

        // Build dynamic query with filters
        $conditions = [];
        $params     = [];

        if ($q !== '') {
            $conditions[] = "(e.description LIKE :q OR e.vendor LIKE :q2 OR e.notes LIKE :q3)";
            $params['q']  = "%{$q}%";
            $params['q2'] = "%{$q}%";
            $params['q3'] = "%{$q}%";
        }

        if ($from !== '') {
            $conditions[] = "e.expense_date >= :from";
            $params['from'] = $from;
        }

        if ($to !== '') {
            $conditions[] = "e.expense_date <= :to";
            $params['to'] = $to;
        }

        if ($category !== '') {
            $conditions[] = "e.category_id = :category_id";
            $params['category_id'] = (int) $category;
        }

        $where  = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $offset = ($page - 1) * $perPage;

        $db = $expenseModel->getDb();

        // Count total
        $countSql = "SELECT COUNT(*) FROM expenses e {$where}";
        $countStmt = $db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(":{$key}", $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        // Fetch page of expenses with category name
        $sql = "SELECT e.*, c.name AS category_name, c.color AS category_color, r.title AS report_title
                FROM expenses e
                LEFT JOIN expense_categories c ON e.category_id = c.id
                LEFT JOIN expense_reports r ON e.report_id = r.id
                {$where}
                ORDER BY e.expense_date DESC, e.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $expenses = $stmt->fetchAll();

        // Totals for filtered results
        $totalsSql = "SELECT
                        COALESCE(SUM(CASE WHEN e.type = 'debit' THEN e.amount ELSE 0 END), 0) AS total_debits,
                        COALESCE(SUM(CASE WHEN e.type = 'credit' THEN e.amount ELSE 0 END), 0) AS total_credits
                      FROM expenses e {$where}";
        $totalsStmt = $db->prepare($totalsSql);
        foreach ($params as $key => $value) {
            $totalsStmt->bindValue(":{$key}", $value);
        }
        $totalsStmt->execute();
        $totals = $totalsStmt->fetch();

        $pagination = [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => (int) ceil($total / $perPage),
        ];

        $this->view('expenses.ledger.index', [
            'expenses'   => $expenses,
            'categories' => $categories,
            'pagination' => $pagination,
            'totals'     => $totals,
            'filters'    => compact('q', 'from', 'to', 'category'),
        ]);
    }

    /**
     * Show the create expense form
     */
    public function create(): void
    {
        $this->requireAuth();

        $categoryModel = new Category();
        $reportModel   = new ExpenseReport();

        $categories = $categoryModel->getActive();
        $reports    = $reportModel->all('title', 'ASC');

        $this->view('expenses.ledger.form', [
            'categories' => $categories,
            'reports'    => $reports,
        ]);
    }

    /**
     * Store a new expense
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        // Validate
        $errors = $this->validate();
        if (!empty($errors)) {
            $this->setFlash('danger', implode('<br>', $errors));
            $this->redirect('/expenses/create');
        }

        $expenseModel = new Expense();
        $data = $this->buildExpenseData();

        $expenseModel->create($data);

        // Update report total if linked
        if (!empty($data['report_id'])) {
            $reportModel = new ExpenseReport();
            $reportModel->updateTotal((int) $data['report_id']);
        }

        $this->setFlash('success', 'Expense created successfully.');
        $this->redirect('/expenses');
    }

    /**
     * Show the edit expense form
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $expenseModel  = new Expense();
        $categoryModel = new Category();
        $reportModel   = new ExpenseReport();

        $expense = $expenseModel->find($id);
        if (!$expense) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $categories = $categoryModel->getActive();
        $reports    = $reportModel->all('title', 'ASC');

        $this->view('expenses.ledger.form', [
            'expense'    => $expense,
            'categories' => $categories,
            'reports'    => $reports,
        ]);
    }

    /**
     * Update an existing expense
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $expenseModel = new Expense();
        $expense = $expenseModel->find($id);
        if (!$expense) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        // Validate
        $errors = $this->validate();
        if (!empty($errors)) {
            $this->setFlash('danger', implode('<br>', $errors));
            $this->redirect("/expenses/{$id}/edit");
        }

        $oldReportId = $expense['report_id'] ?? null;
        $data = $this->buildExpenseData();

        $expenseModel->update($id, $data);

        // Update report totals for both old and new reports
        $reportModel = new ExpenseReport();
        if ($oldReportId) {
            $reportModel->updateTotal((int) $oldReportId);
        }
        if (!empty($data['report_id']) && $data['report_id'] != $oldReportId) {
            $reportModel->updateTotal((int) $data['report_id']);
        }

        $this->setFlash('success', 'Expense updated successfully.');
        $this->redirect('/expenses');
    }

    /**
     * Delete an expense
     */
    public function destroy(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $expenseModel = new Expense();
        $expense = $expenseModel->find($id);
        if (!$expense) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $reportId = $expense['report_id'] ?? null;

        $expenseModel->delete($id);

        // Update report total if the expense was linked
        if ($reportId) {
            $reportModel = new ExpenseReport();
            $reportModel->updateTotal((int) $reportId);
        }

        $this->setFlash('success', 'Expense deleted successfully.');
        $this->redirect('/expenses');
    }

    /**
     * Parse voice/text input into structured expense data via JSON endpoint
     */
    public function voiceInput(): void
    {
        $this->requireAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        $text  = trim($input['text'] ?? '');

        if ($text === '') {
            $this->json(['error' => 'No text provided'], 400);
        }

        // Parse amount — look for dollar amounts like $12.50, 12.50, 1234
        $amount = null;
        if (preg_match('/\$?([\d,]+(?:\.\d{1,2})?)/', $text, $m)) {
            $amount = (float) str_replace(',', '', $m[1]);
        }

        // Parse date — look for "today", "yesterday", or date patterns
        $date = date('Y-m-d');
        if (preg_match('/yesterday/i', $text)) {
            $date = date('Y-m-d', strtotime('-1 day'));
        } elseif (preg_match('/(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?/', $text, $dm)) {
            $year = isset($dm[3]) ? (strlen($dm[3]) === 2 ? '20' . $dm[3] : $dm[3]) : date('Y');
            $date = sprintf('%s-%02d-%02d', $year, (int) $dm[1], (int) $dm[2]);
        }

        // Try to match a category
        $categoryModel = new Category();
        $categories    = $categoryModel->getActive();
        $matchedCategory = null;
        $lowerText = strtolower($text);

        foreach ($categories as $cat) {
            if (stripos($lowerText, strtolower($cat['name'])) !== false) {
                $matchedCategory = $cat;
                break;
            }
        }

        // Build description — remove the amount and date fragments
        $description = $text;
        $description = preg_replace('/\$?[\d,]+(?:\.\d{1,2})?/', '', $description);
        $description = preg_replace('/\b(today|yesterday)\b/i', '', $description);
        $description = preg_replace('/\d{1,2}[\/\-]\d{1,2}(?:[\/\-]\d{2,4})?/', '', $description);
        if ($matchedCategory) {
            $description = preg_replace('/\b' . preg_quote($matchedCategory['name'], '/') . '\b/i', '', $description);
        }
        $description = preg_replace('/\b(for|at|on|spent|paid|bought|in)\b/i', '', $description);
        $description = trim(preg_replace('/\s{2,}/', ' ', $description));

        // Determine type — default to debit unless "income", "received", "refund", "credit" found
        $type = 'debit';
        if (preg_match('/\b(income|received|refund|credit|earned)\b/i', $text)) {
            $type = 'credit';
        }

        $this->json([
            'description' => $description ?: null,
            'amount'      => $amount,
            'date'        => $date,
            'type'        => $type,
            'category_id' => $matchedCategory['id'] ?? null,
            'category'    => $matchedCategory['name'] ?? null,
        ]);
    }

    /**
     * Validate expense form input, returning an array of error messages
     */
    private function validate(): array
    {
        $errors = [];

        if (empty(trim($_POST['description'] ?? ''))) {
            $errors[] = 'Description is required.';
        }

        if (!isset($_POST['amount']) || $_POST['amount'] === '' || !is_numeric($_POST['amount'])) {
            $errors[] = 'A valid amount is required.';
        }

        if (empty($_POST['expense_date'])) {
            $errors[] = 'Expense date is required.';
        }

        return $errors;
    }

    /**
     * Build the data array from POST input for create/update
     */
    private function buildExpenseData(): array
    {
        $data = [
            'description'  => trim($_POST['description']),
            'amount'       => (float) $_POST['amount'],
            'expense_date' => $_POST['expense_date'],
            'type'         => in_array($_POST['type'] ?? 'debit', ['debit', 'credit']) ? $_POST['type'] : 'debit',
            'user_id'      => $_SESSION['user_id'],
        ];

        if (!empty($_POST['category_id'])) {
            $data['category_id'] = (int) $_POST['category_id'];
        } else {
            $data['category_id'] = null;
        }

        if (!empty($_POST['vendor'])) {
            $data['vendor'] = trim($_POST['vendor']);
        } else {
            $data['vendor'] = null;
        }

        if (!empty($_POST['report_id'])) {
            $data['report_id'] = (int) $_POST['report_id'];
        } else {
            $data['report_id'] = null;
        }

        if (!empty($_POST['notes'])) {
            $data['notes'] = trim($_POST['notes']);
        } else {
            $data['notes'] = null;
        }

        return $data;
    }
}
