<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Visitor counter: sent in the background by every public page.
        // Only stores anonymous counts, so it doesn't need a form token
        // (a tab left open for hours would otherwise get "419 expired").
        't/hit',
        't/ping',
        'ad/*/view',   // pop-up ad view counter
    ];
}
