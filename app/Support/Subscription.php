<?php

namespace App\Support;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Cookie;

/**
 * Newsletter subscription state + subscribe pop-up settings.
 *
 *  - Who is subscribed?  a long-lived cookie (um_sub) that points to the subscriber record,
 *    or – for someone logged in – a subscriber record with their e-mail.
 *  - The browser also keeps a small flag (localStorage) for anonymous visitors.
 *  - Pop-up settings: Admin → Website → Subscribe Pop-up (settings table, group "subscribe_popup").
 */
class Subscription
{
    public const COOKIE = 'um_sub';

    /** Setting key => [label, default] */
    public const FIELDS = [
        'subpop_enabled' => ['Show the subscribe pop-up automatically', '1'],
        'subpop_delay' => ['Show after (seconds)', '10'],
        'subpop_interval' => ['Show again after closing', '1440'],
        'google_client_id' => ['Google Client ID', ''],
        'google_client_secret' => ['Google Client secret', ''],
        'linkedin_client_id' => ['LinkedIn Client ID', ''],
        'linkedin_client_secret' => ['LinkedIn Client secret', ''],
    ];

    /** Minutes => label */
    public const INTERVALS = [
        5 => '5 minutes',
        15 => '15 minutes',
        30 => '30 minutes',
        60 => '1 hour',
        1440 => '1 day',
        10080 => '1 week',
    ];

    public static function settings(): array
    {
        $out = [];
        foreach (self::FIELDS as $key => [, $default]) {
            $out[$key] = (string) SiteSettings::get($key, $default);
        }

        return $out;
    }

    /** Is "Continue with Google / LinkedIn" set up? */
    public static function providerReady(string $provider): bool
    {
        return SiteSettings::get($provider . '_client_id') && SiteSettings::get($provider . '_client_secret');
    }

    /**
     * 'yes'     – this visitor is an active subscriber
     * 'no'      – they were, but the record is now unsubscribed / deleted
     * 'unknown' – no information on the server (the browser flag decides)
     */
    public static function state(): string
    {
        try {
            $id = (int) request()->cookie(self::COOKIE);

            if ($id > 0) {
                $subscriber = Subscriber::find($id);

                return $subscriber && (int) $subscriber->status === 1 ? 'yes' : 'no';
            }

            if (auth()->check() && Subscriber::where('email', auth()->user()->email)->where('status', 1)->exists()) {
                return 'yes';
            }
        } catch (\Throwable $e) {
            // fall through
        }

        return 'unknown';
    }

    /**
     * Add (or re-activate) a subscriber and remember this browser as subscribed.
     *
     * @return array{0: Subscriber, 1: bool}  [subscriber, was already subscribed]
     */
    public static function subscribe(string $email, string $source = 'website', array $extra = []): array
    {
        $email = strtolower(trim($email));
        $existing = Subscriber::withDeleted()->where('email', $email)->first();
        $already = $existing && (int) $existing->status === 1;

        $data = array_filter([
            'name' => $extra['name'] ?? null,
            'phone' => $extra['phone'] ?? null,
            'ip_address' => request()->ip(),
        ], fn ($v) => $v !== null && $v !== '');

        if ($existing) {
            // keep how they first subscribed; just make sure the record is active again
            $existing->fill($data + ['status' => 1, 'unsubscribed_at' => null]);
            if (!$already) {
                $existing->subscribed_at = now();
                $existing->source = $source;
            }
            $existing->save();
            $subscriber = $existing;
        } else {
            $subscriber = Subscriber::create($data + [
                'email' => $email,
                'source' => $source,
                'subscribed_at' => now(),
                'status' => 1,
            ]);
        }

        self::remember($subscriber);

        return [$subscriber, $already];
    }

    /** Cookie for 400 days: "this browser belongs to subscriber #id". */
    public static function remember(Subscriber $subscriber): void
    {
        Cookie::queue(Cookie::make(self::COOKIE, (string) $subscriber->id, 60 * 24 * 400, null, null, null, true, false, 'Lax'));
    }
}
