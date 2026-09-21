<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Support\SiteSettings;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Writes the stored brand tokens into the document as CSS custom properties.
 *
 * Rendered once in the layout head. Because the settings row is cached and the
 * cache is cleared the moment it is saved, changing a colour in the admin panel
 * shows up on the very next request — no rebuild, no deploy, no cache command.
 */
final class ThemeVars extends Component
{
    public function __construct(private readonly SiteSettings $settings) {}

    public function render(): View
    {
        return view('components.theme-vars', [
            'tokens' => collect($this->settings->themeTokens())
                ->map(fn (string $value): string => self::sanitise($value))
                ->filter(fn (string $value): bool => $value !== '')
                ->all(),
        ]);
    }

    /**
     * Makes an admin-entered token safe to emit inside a style block.
     *
     * Font stacks legitimately contain quotes, so escaping them the way Blade
     * escapes HTML would break the declaration. Instead the few characters that
     * could end the block or start a new rule are removed outright.
     */
    private static function sanitise(string $value): string
    {
        return trim(str_replace(['<', '>', '{', '}', ';', '@', '\\'], '', $value));
    }
}
