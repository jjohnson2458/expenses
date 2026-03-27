<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function switch(string $locale)
    {
        if (!in_array($locale, ['en', 'es'])) {
            $locale = 'en';
        }

        session(['lang' => $locale]);

        if (Auth::check()) {
            Auth::user()->update(['lang' => $locale]);
        }

        return redirect()->back();
    }
}
