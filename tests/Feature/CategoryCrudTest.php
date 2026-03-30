<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function authUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_can_list_categories(): void
    {
        $user = $this->authUser();
        Category::create(['name' => 'Food', 'is_active' => 1, 'sort_order' => 0]);

        $response = $this->get('/categories');
        $response->assertStatus(200);
    }

    public function test_can_create_category(): void
    {
        $user = $this->authUser();

        $response = $this->post('/categories', [
            'name' => 'Office Supplies',
            'color' => '#ff0000',
            'icon' => 'bi-pencil',
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('expense_categories', ['name' => 'Office Supplies']);
    }

    public function test_can_update_category(): void
    {
        $user = $this->authUser();
        $cat = Category::create(['name' => 'Old', 'is_active' => 1, 'sort_order' => 0]);

        $response = $this->post("/categories/{$cat->id}", [
            'name' => 'New Name',
            'color' => '#00ff00',
        ]);

        $this->assertDatabaseHas('expense_categories', ['id' => $cat->id, 'name' => 'New Name']);
    }

    public function test_can_delete_category(): void
    {
        $user = $this->authUser();
        $cat = Category::create(['name' => 'Delete Me', 'is_active' => 1, 'sort_order' => 0]);

        $response = $this->post("/categories/{$cat->id}/delete");

        $this->assertDatabaseMissing('expense_categories', ['id' => $cat->id]);
    }
}
