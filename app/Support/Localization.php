<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * Which language the current request is in.
 *
 * Resolution order: an explicit ?lang= on the URL, then the choice already made
 * this session, then the signed-in customer's own preference, then English. A
 * customer who asked for Bengali gets Bengali in the portal and in every
 * notification without choosing again each visit.
 */
final class Localization
{
    public const SUPPORTED = ['en' => 'English', 'bn' => 'বাংলা'];

    public const SESSION_KEY = 'app_locale';

    public static function resolve(?string $requested = null): string
    {
        $candidates = [
            $requested,
            Session::get(self::SESSION_KEY),
            auth('customer')->user()?->preferred_locale,
            config('app.locale'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && array_key_exists($candidate, self::SUPPORTED)) {
                return $candidate;
            }
        }

        return 'en';
    }

    public static function isBengali(): bool
    {
        return app()->getLocale() === 'bn';
    }

    public static function other(): string
    {
        return self::isBengali() ? 'en' : 'bn';
    }

    public static function label(string $locale): string
    {
        return self::SUPPORTED[$locale] ?? $locale;
    }
}
