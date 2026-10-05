<?php

namespace App\Http\Controllers;

use App\Support\SiteSettings;
use App\Support\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * "Continue with Google / LinkedIn" in the subscribe pop-up.
 *
 *   /subscribe/google            → sends the visitor to Google
 *   /subscribe/google/callback   → Google sends them back with their e-mail → subscribed
 *   (same for /subscribe/linkedin)
 *
 * Keys: Admin → Website → Subscribe Pop-up. Only name + e-mail are read; nobody is "logged in".
 */
class SubscribeOAuthController extends Controller
{
    protected const PROVIDERS = [
        'google' => [
            'auth' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token' => 'https://oauth2.googleapis.com/token',
            'user' => 'https://openidconnect.googleapis.com/v1/userinfo',
            'scope' => 'openid email profile',
            'label' => 'Google',
        ],
        'linkedin' => [
            'auth' => 'https://www.linkedin.com/oauth/v2/authorization',
            'token' => 'https://www.linkedin.com/oauth/v2/accessToken',
            'user' => 'https://api.linkedin.com/v2/userinfo',
            'scope' => 'openid profile email',
            'label' => 'LinkedIn',
        ],
    ];

    public function redirect(Request $request, string $provider)
    {
        $config = self::PROVIDERS[$provider];

        if (!Subscription::providerReady($provider)) {
            return $this->back($request)->with('subscribe_error', $config['label'] . ' sign-up is not set up yet. Please use your e-mail address.');
        }

        $state = Str::random(40);
        $request->session()->put('subscribe_oauth', [
            'state' => $state,
            'provider' => $provider,
            'return' => $this->safeReturn($request->query('return') ?: url()->previous()),
        ]);

        return redirect()->away($config['auth'] . '?' . http_build_query(array_filter([
            'client_id' => SiteSettings::get($provider . '_client_id'),
            'redirect_uri' => route('subscribe.oauth.callback', $provider),
            'response_type' => 'code',
            'scope' => $config['scope'],
            'state' => $state,
            'prompt' => $provider === 'google' ? 'select_account' : null,
        ])));
    }

    public function callback(Request $request, string $provider)
    {
        $config = self::PROVIDERS[$provider];
        $saved = (array) $request->session()->pull('subscribe_oauth', []);
        $return = $saved['return'] ?? route('home');

        // The visitor pressed "Cancel", or the request did not start on our site
        if ($request->filled('error') || !$request->filled('code')
            || empty($saved['state']) || !hash_equals($saved['state'], (string) $request->query('state'))
            || ($saved['provider'] ?? null) !== $provider) {
            return redirect()->to($return)->with('subscribe_error', $config['label'] . ' sign-up was cancelled. You can try again or use your e-mail address.');
        }

        try {
            $token = Http::asForm()->timeout(15)->post($config['token'], [
                'grant_type' => 'authorization_code',
                'code' => $request->query('code'),
                'redirect_uri' => route('subscribe.oauth.callback', $provider),
                'client_id' => SiteSettings::get($provider . '_client_id'),
                'client_secret' => SiteSettings::get($provider . '_client_secret'),
            ])->throw()->json('access_token');

            $profile = Http::withToken($token)->timeout(15)->get($config['user'])->throw()->json();
            $email = $profile['email'] ?? null;

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('No e-mail address returned by ' . $provider);
            }

            [, $already] = Subscription::subscribe($email, $provider, ['name' => $profile['name'] ?? null]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->to($return)->with('subscribe_error', 'We could not complete the ' . $config['label'] . ' sign-up. Please try again or use your e-mail address.');
        }

        return redirect()->to($return)->with('subscribed', $already
            ? 'You are already subscribed — thank you!'
            : 'Thank you for subscribing to ' . SiteSettings::get('site_name', 'The Unicorn Magazine') . '!');
    }

    /** Only return to pages on this website. */
    protected function safeReturn(?string $url): string
    {
        $home = route('home');
        $root = rtrim(url('/'), '/');

        if (!$url || !Str::startsWith($url, $root) || Str::contains($url, '/subscribe/')) {
            return $home;
        }

        return $url;
    }

    protected function back(Request $request)
    {
        return redirect()->to($this->safeReturn(url()->previous()));
    }
}
