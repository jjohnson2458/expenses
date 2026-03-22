<?php
/**
 * Category Controller — CRUD for expense categories
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * List all categories (paginated, ordered by sort_order)
     */
    public function index(): void
    {
        $this->requireAuth();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $categoryModel = new Category();
        $pagination = $categoryModel->paginate($page, 20, 'sort_order', 'ASC');

        $this->view('expenses.categories.index', [
            'categories' => $pagination['data'],
            'pagination'  => $pagination,
        ]);
    }

    /**
     * Show the create-category form
     */
    public function create(): void
    {
        $this->requireAuth();

        $this->view('expenses.categories.form', [
            'category' => null,
        ]);
    }

    /**
     * Store a new category
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->setFlash('danger', 'Category name is required.');
            $this->redirect('/categories/create');
        }

        $categoryModel = new Category();
        $categoryModel->create([
            'name'        => $name,
            'name_es'     => trim($_POST['name_es'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'color'       => $_POST['color'] ?? '#6c757d',
            'icon'        => trim($_POST['icon'] ?? ''),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ]);

        $this->setFlash('success', 'Category created successfully.');
        $this->redirect('/categories');
    }

    /**
     * Show the edit-category form
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $this->view('expenses.categories.form', [
            'category' => $category,
        ]);
    }

    /**
     * Update an existing category
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->setFlash('danger', 'Category name is required.');
            $this->redirect("/categories/{$id}/edit");
        }

        $categoryModel = new Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            http_response_code(404);
            $this->view('pages.404');
            return;
        }

        $categoryModel->update($id, [
            'name'        => $name,
            'name_es'     => trim($_POST['name_es'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'color'       => $_POST['color'] ?? '#6c757d',
            'icon'        => trim($_POST['icon'] ?? ''),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ]);

        $this->setFlash('success', 'Category updated successfully.');
        $this->redirect('/categories');
    }

    /**
     * Delete a category
     */
    public function destroy(int $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $categoryModel = new Category();
        $categoryModel->delete($id);

        $this->setFlash('success', 'Category deleted successfully.');
        $this->redirect('/categories');
    }

    /**
     * Reorder categories via AJAX
     *
     * Expects JSON body: [ { "id": 1, "position": 0 }, ... ]
     */
    public function reorder(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            $this->json(['success' => false, 'message' => 'Invalid payload.'], 400);
        }

        $orders = [];
        foreach ($input as $item) {
            if (isset($item['id'], $item['position'])) {
                $orders[(int) $item['id']] = (int) $item['position'];
            }
        }

        $categoryModel = new Category();
        $categoryModel->updateSortOrder($orders);

        $this->json(['success' => true, 'message' => 'Sort order updated.']);
    }
}
