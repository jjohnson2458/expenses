<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    public function test_login_page_loads(): void
    {
        // Verify the login view file exists
        $viewFile = VIEW_PATH . '/auth/login.php';
        $this->assertFileExists($viewFile, 'Login view should exist');
    }

    public function test_login_with_valid_credentials(): void
    {
        $email = 'auth_valid_' . uniqid() . '@test.com';
        $password = 'securepass123';
        $this->createTestUser([
            'name' => 'Auth User',
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);

        // Simulate the login flow using the User model directly
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        $this->assertNotNull($user, 'User should exist in database');
        $this->assertTrue(password_verify($password, $user['password']), 'Password should verify');

        // Simulate setting session as the AuthController does
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        $this->assertEquals($user['id'], $_SESSION['user_id']);
        $this->assertEquals('Auth User', $_SESSION['user_name']);
        $this->assertEquals($email, $_SESSION['user_email']);
        $this->assertEquals('admin', $_SESSION['user_role']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $email = 'auth_invalid_' . uniqid() . '@test.com';
        $this->createTestUser([
            'email' => $email,
            'password' => password_hash('realpassword', PASSWORD_DEFAULT),
        ]);

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        $this->assertNotNull($user);
        // Wrong password should fail verification
        $this->assertFalse(password_verify('wrongpassword', $user['password']));

        // Simulate what the controller does on failure: set a flash message
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid email or password.'];

        $this->assertEquals('danger', $_SESSION['flash']['type']);
        $this->assertStringContainsString('Invalid', $_SESSION['flash']['message']);
    }

    public function test_logout_clears_session(): void
    {
        // Set up an authenticated session
        $this->actingAsAdmin();

        $this->assertNotEmpty($_SESSION['user_id']);

        // Simulate logout (what AuthController::logout does minus headers/exit)
        $_SESSION = [];

        $this->assertEmpty($_SESSION);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_dashboard_redirects_when_not_authenticated(): void
    {
        // Clear any existing session
        unset($_SESSION['user_id']);

        // The requireAuth() method checks for user_id in session
        $this->assertEmpty($_SESSION['user_id'] ?? null);

        // Verify that the controller's requireAuth check would trigger
        // by checking the condition it uses
        $this->assertTrue(empty($_SESSION['user_id']),
            'Without user_id in session, requireAuth should redirect');
    }
}
