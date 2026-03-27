<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    // Stripe price IDs
    private const PRICES = [
        'solo_monthly'  => 'price_1TFg4QHEV4gYSVRqo0fuKsfn',
        'solo_annual'   => 'price_1TFg4QHEV4gYSVRquBx00MV3',
        'pro_monthly'   => 'price_1TFg4QHEV4gYSVRqtyUazatI',
        'pro_annual'    => 'price_1TFg4RHEV4gYSVRqOooyUBXl',
        'team_monthly'  => 'price_1TFg4RHEV4gYSVRqkESJsKOY',
    ];

    public function index()
    {
        $user = Auth::user();

        return view('billing.index', [
            'user' => $user,
            'currentPlan' => $this->getCurrentPlan($user),
            'subscription' => $user->subscription('default'),
            'onGracePeriod' => $user->subscription('default')?->onGracePeriod() ?? false,
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:solo_monthly,solo_annual,pro_monthly,pro_annual,team_monthly',
        ]);

        $user = Auth::user();
        $priceId = self::PRICES[$request->plan];

        return $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => url('/billing?success=1'),
                'cancel_url' => url('/billing?canceled=1'),
            ]);
    }

    public function changePlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:solo_monthly,solo_annual,pro_monthly,pro_annual,team_monthly',
        ]);

        $user = Auth::user();
        $priceId = self::PRICES[$request->plan];

        $user->subscription('default')->swap($priceId);

        return redirect('/billing')->with('flash', [
            'type' => 'success',
            'message' => 'Your plan has been updated.',
        ]);
    }

    public function cancel()
    {
        Auth::user()->subscription('default')->cancel();

        return redirect('/billing')->with('flash', [
            'type' => 'info',
            'message' => 'Your subscription has been canceled. You\'ll retain access until the end of your billing period.',
        ]);
    }

    public function resume()
    {
        Auth::user()->subscription('default')->resume();

        return redirect('/billing')->with('flash', [
            'type' => 'success',
            'message' => 'Your subscription has been resumed.',
        ]);
    }

    public function portal()
    {
        return Auth::user()->redirectToBillingPortal(url('/billing'));
    }

    private function getCurrentPlan($user): string
    {
        $subscription = $user->subscription('default');

        if (!$subscription || $subscription->canceled() && !$subscription->onGracePeriod()) {
            return 'free';
        }

        $priceId = $subscription->stripe_price;
        $planMap = array_flip(self::PRICES);

        $key = $planMap[$priceId] ?? 'free';

        if (str_starts_with($key, 'solo')) return 'solo';
        if (str_starts_with($key, 'pro')) return 'pro';
        if (str_starts_with($key, 'team')) return 'team';

        return 'free';
    }
}
