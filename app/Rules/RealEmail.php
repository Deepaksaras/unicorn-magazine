<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

/**
 * Stricter e-mail check for the public forms (subscribe, contact, advertise).
 *
 *  1. Correct format            name@domain.tld
 *  2. Common typing mistakes    gmial.com, gmail.con …  → asks the visitor to correct it
 *  3. Throw-away addresses      mailinator, yopmail …    → refused
 *  4. The domain really exists and can receive mail (DNS lookup)
 *
 * It cannot prove that the mailbox belongs to the visitor – only a confirmation
 * e-mail with a link can do that.
 */
class RealEmail implements ValidationRule
{
    /** Typing mistake => what the visitor most likely meant */
    protected const TYPOS = [
        'gmial.com' => 'gmail.com', 'gmai.com' => 'gmail.com', 'gamil.com' => 'gmail.com', 'gnail.com' => 'gmail.com',
        'gmail.co' => 'gmail.com', 'gmail.con' => 'gmail.com', 'gmail.cm' => 'gmail.com', 'gmaill.com' => 'gmail.com',
        'gmail.comm' => 'gmail.com', 'gmal.com' => 'gmail.com', 'gmail.om' => 'gmail.com', 'gmail.in' => 'gmail.com',
        'yaho.com' => 'yahoo.com', 'yahooo.com' => 'yahoo.com', 'yahoo.con' => 'yahoo.com', 'yhoo.com' => 'yahoo.com',
        'hotmial.com' => 'hotmail.com', 'hotmal.com' => 'hotmail.com', 'hotmail.con' => 'hotmail.com', 'hotmil.com' => 'hotmail.com',
        'outlok.com' => 'outlook.com', 'outloook.com' => 'outlook.com', 'outlook.con' => 'outlook.com',
        'rediffmail.con' => 'rediffmail.com', 'redifmail.com' => 'rediffmail.com', 'icloud.con' => 'icloud.com',
    ];

    protected const DISPOSABLE = [
        'mailinator.com', 'yopmail.com', 'guerrillamail.com', 'guerrillamail.net', 'sharklasers.com', '10minutemail.com',
        'tempmail.com', 'temp-mail.org', 'tempmail.net', 'throwawaymail.com', 'trashmail.com', 'getnada.com', 'dispostable.com',
        'maildrop.cc', 'fakeinbox.com', 'tempinbox.com', 'mintemail.com', 'emailondeck.com', 'moakt.com', 'mohmal.com',
        'tmpmail.org', 'tmpmail.net', 'mailnesia.com', 'spamgourmet.com', 'discard.email', 'burnermail.io', 'example.com',
        'test.com', 'email.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower(trim((string) $value));

        // 1. format: one @, sensible name part, domain with a real ending (.com, .in, .co.in …)
        $ok = filter_var($email, FILTER_VALIDATE_EMAIL)
            && preg_match('/^[a-z0-9](?:[a-z0-9._%+\-]{0,62}[a-z0-9_])?@(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/', $email)
            && !str_contains($email, '..');

        if (!$ok) {
            $fail('Please enter a valid e-mail address, for example name@example.com.');

            return;
        }

        $domain = substr($email, strrpos($email, '@') + 1);

        // 2. typing mistakes
        if (isset(self::TYPOS[$domain])) {
            $fail('Did you mean ' . substr($email, 0, strrpos($email, '@') + 1) . self::TYPOS[$domain] . '? Please check your e-mail address.');

            return;
        }

        // 3. throw-away addresses
        if (in_array($domain, self::DISPOSABLE, true)) {
            $fail('Please use your regular e-mail address (temporary addresses are not accepted).');

            return;
        }

        // 4. the domain exists and accepts mail
        if (!$this->domainAcceptsMail($domain)) {
            $fail('We could not find the e-mail provider "' . $domain . '". Please check your e-mail address.');
        }
    }

    protected function lookup(string $domain): bool
    {
        $host = $domain . '.';

        return @checkdnsrr($host, 'MX') || @checkdnsrr($host, 'A') || @checkdnsrr($host, 'AAAA');
    }

    protected function domainAcceptsMail(string $domain): bool
    {
        if (!function_exists('checkdnsrr')) {
            return true;                                    // server cannot look it up → do not block visitors
        }

        try {
            $found = (bool) Cache::remember('email-domain:' . $domain, 86400, fn () => $this->lookup($domain) ? 1 : 0);

            if ($found) {
                return true;
            }

            // If even gmail.com cannot be found, the server's DNS is not answering → do not block visitors
            $dnsWorks = (bool) Cache::remember('email-domain:dns-works', 600, fn () => $this->lookup('gmail.com') ? 1 : 0);

            return !$dnsWorks;
        } catch (\Throwable $e) {
            return true;
        }
    }
}
