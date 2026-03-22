<?php
/**
 * Settings Controller — user profile and preferences
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\User;

class SettingsController extends Controller
{
    /**
     * Show the settings page
     */
    public function index(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $user = $userModel->find((int) $_SESSION['user_id']);

        $this->view('settings.index', [
            'profile' => $user,
        ]);
    }

    /**
     * Update user settings
     */
    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $userModel = new User();
        $userId    = (int) $_SESSION['user_id'];
        $user      = $userModel->find($userId);

        if (!$user) {
            $this->setFlash('danger', 'User not found.');
            $this->redirect('/settings');
        }

        $section = $_POST['section'] ?? 'profile';

        switch ($section) {
            case 'profile':
                $this->updateProfile($userModel, $userId);
                break;

            case 'password':
                $this->updatePassword($userModel, $userId, $user);
                break;

            case 'preferences':
                $this->updatePreferences($userModel, $userId);
                break;

            default:
                $this->setFlash('danger', 'Unknown settings section.');
        }

        $this->redirect('/settings');
    }

    /**
     * Update profile (name, email)
     */
    private function updateProfile(User $userModel, int $userId): void
    {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $this->setFlash('danger', 'Name and email are required.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('danger', 'Please enter a valid email address.');
            return;
        }

        // Check for duplicate email
        $existing = $userModel->findByEmail($email);
        if ($existing && (int) $existing['id'] !== $userId) {
            $this->setFlash('danger', 'That email address is already in use.');
            return;
        }

        $userModel->update($userId, [
            'name'  => $name,
            'email' => $email,
        ]);

        // Update session
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['name']  = $name;
            $_SESSION['user']['email'] = $email;
        }

        $this->setFlash('success', 'Profile updated successfully.');
    }

    /**
     * Update password (verify current password first)
     */
    private function updatePassword(User $userModel, int $userId, array $user): void
    {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $this->setFlash('danger', 'All password fields are required.');
            return;
        }

        if (!$userModel->verifyPassword($current, $user['password'])) {
            $this->setFlash('danger', 'Current password is incorrect.');
            return;
        }

        if ($new !== $confirm) {
            $this->setFlash('danger', 'New passwords do not match.');
            return;
        }

        if (strlen($new) < 8) {
            $this->setFlash('danger', 'New password must be at least 8 characters.');
            return;
        }

        $userModel->updatePassword($userId, $new);

        $this->setFlash('success', 'Password changed successfully.');
    }

    /**
     * Update preferences (language)
     */
    private function updatePreferences(User $userModel, int $userId): void
    {
        $lang = $_POST['lang'] ?? 'en';
        if (!in_array($lang, ['en', 'es'])) {
            $lang = 'en';
        }

        $userModel->update($userId, ['lang' => $lang]);
        $_SESSION['lang'] = $lang;

        $this->setFlash('success', 'Preferences updated successfully.');
    }
}
