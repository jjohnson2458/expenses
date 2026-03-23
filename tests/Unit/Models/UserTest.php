<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User();
    }

    public function test_can_create_user(): void
    {
        $id = $this->model->create([
            'name' => 'John Doe',
            'email' => 'john_' . uniqid() . '@example.com',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
            'role' => 'user',
        ]);

        $this->assertGreaterThan(0, $id);

        $user = $this->model->find($id);
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user['name']);
        $this->assertEquals('user', $user['role']);
    }

    public function test_find_by_email(): void
    {
        $email = 'findme_' . uniqid() . '@example.com';
        $this->createTestUser(['name' => 'Findable', 'email' => $email]);

        $found = $this->model->findByEmail($email);
        $this->assertNotNull($found);
        $this->assertEquals('Findable', $found['name']);
        $this->assertEquals($email, $found['email']);
    }

    public function test_find_by_email_returns_null_for_unknown(): void
    {
        $result = $this->model->findByEmail('nonexistent_' . uniqid() . '@example.com');
        $this->assertNull($result);
    }

    public function test_verify_password(): void
    {
        $hash = password_hash('correctpassword', PASSWORD_DEFAULT);

        $this->assertTrue($this->model->verifyPassword('correctpassword', $hash));
        $this->assertFalse($this->model->verifyPassword('wrongpassword', $hash));
    }

    public function test_update_password(): void
    {
        $user = $this->createTestUser();

        $result = $this->model->updatePassword((int) $user['id'], 'newpassword456');
        $this->assertTrue($result);

        $updated = $this->model->find((int) $user['id']);
        $this->assertTrue(password_verify('newpassword456', $updated['password']));
        $this->assertFalse(password_verify('password123', $updated['password']));
    }
}
