<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicSitePayload;
use App\Support\SiteContent;
use App\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

final class HomeController extends Controller
{
    public function __invoke(
        SiteContent $content,
        SiteSettings $settings,
        PublicSitePayload $payload,
    ): View {
        return view('public.home', [
            'c' => $content,
            'settings' => $settings,
            'payload' => $payload->build(),
            'logo' => $this->asset($settings->get('logo_path'), 'images/bdex-logo.png'),
            'favicon' => $this->asset($settings->get('favicon_path'), 'images/bdex-logo.png'),
            'phoneAu' => (string) $settings->get('contact_phone_au'),
            'phoneBd' => (string) $settings->get('contact_phone_bd'),
            'telAu' => $this->tel($settings->get('contact_phone_au')),
            'telBd' => $this->tel($settings->get('contact_phone_bd')),
        ]);
    }

    /** An uploaded file when one exists, otherwise the shipped default. */
    private function asset(mixed $path, string $fallback): string
    {
        return is_string($path) && $path !== ''
            ? Storage::url($path)
            : asset($fallback);
    }

    private function tel(mixed $number): string
    {
        return preg_replace('/[^0-9+]/', '', (string) $number) ?? '';
    }
}
