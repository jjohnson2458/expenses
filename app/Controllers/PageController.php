<?php
/**
 * Page Controller — static/legal pages
 *
 * @author J.J. Johnson <visionquest716@gmail.com>
 */

namespace App\Controllers;

class PageController extends Controller
{
    /**
     * Terms of Service
     */
    public function terms(): void
    {
        $this->view('pages.terms');
    }

    /**
     * Privacy Policy
     */
    public function privacy(): void
    {
        $this->view('pages.privacy');
    }
}
