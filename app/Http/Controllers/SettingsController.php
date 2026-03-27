<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $profile = Auth::user();

        return view('settings.index', compact('profile'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $section = $request->input('section', 'profile');

        switch ($section) {
            case 'profile':
                $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                ]);

                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

                return redirect('/settings')->with('flash', ['type' => 'success', 'message' => 'Profile updated successfully.']);

            case 'password':
                $request->validate([
                    'current_password' => 'required',
                    'new_password' => 'required|min:8|confirmed',
                ], [
                    'new_password.confirmed' => 'New passwords do not match.',
                ]);

                if (!Hash::check($request->current_password, $user->password)) {
                    return redirect('/settings')->with('flash', ['type' => 'danger', 'message' => 'Current password is incorrect.']);
                }

                $user->update(['password' => $request->new_password]);

                return redirect('/settings')->with('flash', ['type' => 'success', 'message' => 'Password changed successfully.']);

            case 'preferences':
                $lang = in_array($request->lang, ['en', 'es']) ? $request->lang : 'en';
                $user->update(['lang' => $lang]);
                session(['lang' => $lang]);

                return redirect('/settings')->with('flash', ['type' => 'success', 'message' => 'Preferences updated successfully.']);

            default:
                return redirect('/settings')->with('flash', ['type' => 'danger', 'message' => 'Unknown settings section.']);
        }
    }
}
