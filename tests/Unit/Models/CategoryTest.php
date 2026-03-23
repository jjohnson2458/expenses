<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    private Category $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Category();
    }

    public function test_can_create_category(): void
    {
        $id = $this->model->create([
            'name' => 'Groceries',
            'description' => 'Food and household items',
            'color' => '#28a745',
            'icon' => 'bi-cart',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $this->assertGreaterThan(0, $id);

        $category = $this->model->find($id);
        $this->assertNotNull($category);
        $this->assertEquals('Groceries', $category['name']);
        $this->assertEquals('#28a745', $category['color']);
    }

    public function test_can_find_category_by_id(): void
    {
        $category = $this->createTestCategory(['name' => 'Utilities']);

        $found = $this->model->find((int) $category['id']);
        $this->assertNotNull($found);
        $this->assertEquals('Utilities', $found['name']);
    }

    public function test_can_update_category(): void
    {
        $category = $this->createTestCategory(['name' => 'Old Name']);

        $result = $this->model->update((int) $category['id'], ['name' => 'New Name']);
        $this->assertTrue($result);

        $updated = $this->model->find((int) $category['id']);
        $this->assertEquals('New Name', $updated['name']);
    }

    public function test_can_delete_category(): void
    {
        $category = $this->createTestCategory();

        $result = $this->model->delete((int) $category['id']);
        $this->assertTrue($result);

        $deleted = $this->model->find((int) $category['id']);
        $this->assertNull($deleted);
    }

    public function test_get_active_returns_only_active(): void
    {
        $this->createTestCategory(['name' => 'Active One', 'is_active' => 1, 'sort_order' => 1]);
        $this->createTestCategory(['name' => 'Active Two', 'is_active' => 1, 'sort_order' => 2]);
        $this->createTestCategory(['name' => 'Inactive', 'is_active' => 0, 'sort_order' => 3]);

        $active = $this->model->getActive();

        // All returned items should be active
        foreach ($active as $item) {
            $this->assertEquals(1, $item['is_active']);
        }

        $names = array_column($active, 'name');
        $this->assertContains('Active One', $names);
        $this->assertContains('Active Two', $names);
        $this->assertNotContains('Inactive', $names);
    }

    public function test_update_sort_order(): void
    {
        $cat1 = $this->createTestCategory(['name' => 'Cat A', 'sort_order' => 0]);
        $cat2 = $this->createTestCategory(['name' => 'Cat B', 'sort_order' => 0]);

        $this->model->updateSortOrder([
            $cat1['id'] => 10,
            $cat2['id'] => 20,
        ]);

        $updated1 = $this->model->find((int) $cat1['id']);
        $updated2 = $this->model->find((int) $cat2['id']);

        $this->assertEquals(10, $updated1['sort_order']);
        $this->assertEquals(20, $updated2['sort_order']);
    }
}
