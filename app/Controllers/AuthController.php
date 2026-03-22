<?php
/**
 * Authentication Controller
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->view('auth.login');
    }

    /**
     * Handle login form submission
     */
    public function login(): void
    {
        $this->verifyCsrf();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->setFlash('danger', 'Please enter both email and password.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->setFlash('danger', 'Invalid email or password.');
            $this->redirect('/login');
        }

        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $this->redirect('/dashboard');
    }

    /**
     * Log the user out
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->redirect('/login');
    }
}
