<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;

class CategoryCrudTest extends TestCase
{
    private Category $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Category();
        $this->actingAsAdmin();
    }

    public function test_can_list_categories(): void
    {
        $this->createTestCategory(['name' => 'Travel', 'sort_order' => 1]);
        $this->createTestCategory(['name' => 'Food', 'sort_order' => 2]);
        $this->createTestCategory(['name' => 'Rent', 'sort_order' => 3]);

        $all = $this->model->all('sort_order', 'ASC');

        $names = array_column($all, 'name');
        $this->assertContains('Travel', $names);
        $this->assertContains('Food', $names);
        $this->assertContains('Rent', $names);
    }

    public function test_can_create_category(): void
    {
        $id = $this->model->create([
            'name' => 'Entertainment',
            'description' => 'Movies, games, etc.',
            'color' => '#ff5733',
            'icon' => 'bi-film',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $this->assertGreaterThan(0, $id);

        $category = $this->model->find($id);
        $this->assertEquals('Entertainment', $category['name']);
        $this->assertEquals('Movies, games, etc.', $category['description']);
        $this->assertEquals('#ff5733', $category['color']);
    }

    public function test_can_update_category(): void
    {
        $category = $this->createTestCategory([
            'name' => 'Before Update',
            'color' => '#000000',
        ]);

        $this->model->update((int) $category['id'], [
            'name' => 'After Update',
            'color' => '#ffffff',
        ]);

        $updated = $this->model->find((int) $category['id']);
        $this->assertEquals('After Update', $updated['name']);
        $this->assertEquals('#ffffff', $updated['color']);
    }

    public function test_can_delete_category(): void
    {
        $category = $this->createTestCategory(['name' => 'To Be Deleted']);

        $this->assertTrue($this->model->delete((int) $category['id']));
        $this->assertNull($this->model->find((int) $category['id']));
    }
}
