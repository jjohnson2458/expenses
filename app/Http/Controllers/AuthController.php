<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('flash', [
                'type' => 'danger',
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // Store legacy session vars for compatibility
            $user = Auth::user();
            session(['lang' => $user->lang ?? 'en']);

            return redirect()->intended('/dashboard');
        }

        RateLimiter::hit($key, 60);

        return back()->with('flash', [
            'type' => 'danger',
            'message' => 'Invalid email or password.',
        ]);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
            'lang' => 'en',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        session(['lang' => 'en']);

        return redirect('/dashboard')->with('flash', [
            'type' => 'success',
            'message' => 'Welcome to VQ Money! Your account has been created.',
        ]);
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Always show success message to prevent email enumeration
        $successMessage = 'If an account exists with that email, we\'ve sent a password reset link.';

        if (!$user) {
            return back()->with('flash', [
                'type' => 'success',
                'message' => $successMessage,
            ]);
        }

        // Generate token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Store new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Build reset URL
        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($request->email));

        // Send email
        Mail::html(
            '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 500px; margin: 0 auto; padding: 40px 20px;">' .
            '<div style="text-align: center; margin-bottom: 30px;">' .
            '<h1 style="color: #1a1c2e; font-size: 24px; margin: 0;">VQ Money</h1>' .
            '<p style="color: #858796; font-size: 14px;">Password Reset Request</p>' .
            '</div>' .
            '<p style="color: #333; font-size: 15px; line-height: 1.6;">You requested a password reset for your VQ Money account. Click the button below to set a new password:</p>' .
            '<div style="text-align: center; margin: 30px 0;">' .
            '<a href="' . $resetUrl . '" style="background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; padding: 12px 32px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 15px; display: inline-block;">Reset Password</a>' .
            '</div>' .
            '<p style="color: #858796; font-size: 13px; line-height: 1.6;">This link will expire in 60 minutes. If you didn\'t request this reset, you can safely ignore this email.</p>' .
            '<hr style="border: none; border-top: 1px solid #e3e6f0; margin: 30px 0;">' .
            '<p style="color: #b0b0b0; font-size: 12px; text-align: center;">&copy; 2026 VisionQuest Services LLC</p>' .
            '</div>',
            function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Reset Your VQ Money Password');
            }
        );

        return back()->with('flash', [
            'type' => 'success',
            'message' => $successMessage,
        ]);
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This reset link has expired. Please request a new one.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No account found with that email address.']);
        }

        $user->password = $request->password;
        $user->save();

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect('/login')->with('flash', [
            'type' => 'success',
            'message' => 'Your password has been reset. Please sign in.',
        ]);
    }

    public function demoLogin(Request $request)
    {
        // Find or create demo user
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@vqmoney.com'],
            [
                'name' => 'Demo User',
                'password' => Str::random(32),
                'role' => 'user',
                'lang' => 'en',
            ]
        );

        Auth::login($demoUser);
        $request->session()->regenerate();
        session(['lang' => 'en']);

        return redirect('/dashboard')->with('flash', [
            'type' => 'info',
            'message' => 'Welcome to the VQ Money demo! Explore the app with sample data.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
