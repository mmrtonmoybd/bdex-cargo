<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the request into the reader's language, and remembers the choice.
 */
final class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Localization::resolve($request->query('lang'));

        if ($request->query('lang') !== null) {
            Session::put(Localization::SESSION_KEY, $locale);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
