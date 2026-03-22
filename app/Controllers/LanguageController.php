<?php
/**
 * Language Controller — switch locale
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

use App\Models\User;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(string $locale): void
    {
        if (!in_array($locale, ['en', 'es'])) {
            $locale = 'en';
        }

        $_SESSION['lang'] = $locale;

        // Persist to database if logged in
        if (!empty($_SESSION['user_id'])) {
            $userModel = new User();
            $userModel->update((int) $_SESSION['user_id'], ['lang' => $locale]);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}
