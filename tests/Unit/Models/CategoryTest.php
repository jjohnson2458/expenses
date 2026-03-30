<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(array $attrs = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Test Category',
            'color' => '#007bff',
            'icon' => 'bi-tag',
            'sort_order' => 0,
            'is_active' => 1,
        ], $attrs));
    }

    public function test_can_create_category(): void
    {
        $cat = $this->makeCategory(['name' => 'Office']);
        $this->assertDatabaseHas('expense_categories', ['name' => 'Office']);
    }

    public function test_can_find_category_by_id(): void
    {
        $cat = $this->makeCategory();
        $found = Category::find($cat->id);
        $this->assertNotNull($found);
        $this->assertEquals($cat->name, $found->name);
    }

    public function test_can_update_category(): void
    {
        $cat = $this->makeCategory();
        $cat->update(['name' => 'Updated Name']);
        $this->assertDatabaseHas('expense_categories', ['id' => $cat->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_category(): void
    {
        $cat = $this->makeCategory();
        $id = $cat->id;
        $cat->delete();
        $this->assertDatabaseMissing('expense_categories', ['id' => $id]);
    }

    public function test_active_scope_returns_only_active(): void
    {
        $this->makeCategory(['name' => 'Active', 'is_active' => 1]);
        $this->makeCategory(['name' => 'Inactive', 'is_active' => 0]);

        $active = Category::active()->get();
        $this->assertEquals(1, $active->count());
        $this->assertEquals('Active', $active->first()->name);
    }

    public function test_ordered_scope(): void
    {
        $this->makeCategory(['name' => 'B', 'sort_order' => 2]);
        $this->makeCategory(['name' => 'A', 'sort_order' => 1]);

        $ordered = Category::ordered()->get();
        $this->assertEquals('A', $ordered->first()->name);
        $this->assertEquals('B', $ordered->last()->name);
    }
}
