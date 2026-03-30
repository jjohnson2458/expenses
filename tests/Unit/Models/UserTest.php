<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'john@test.com', 'name' => 'John Doe']);
    }

    public function test_find_by_email(): void
    {
        $user = User::factory()->create(['email' => 'find@test.com']);

        $found = User::where('email', 'find@test.com')->first();
        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_email_returns_null_for_unknown(): void
    {
        $found = User::where('email', 'nonexistent@test.com')->first();
        $this->assertNull($found);
    }

    public function test_verify_password(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->assertTrue(\Hash::check('secret123', $user->password));
        $this->assertFalse(\Hash::check('wrongpassword', $user->password));
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create(['password' => 'oldpass']);

        $user->update(['password' => 'newpass']);
        $user->refresh();

        $this->assertTrue(\Hash::check('newpass', $user->password));
        $this->assertFalse(\Hash::check('oldpass', $user->password));
    }
}
