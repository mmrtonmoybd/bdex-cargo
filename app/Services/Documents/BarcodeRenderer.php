<?php

declare(strict_types=1);

namespace App\Services\Documents;

use Picqer\Barcode\Renderers\SvgRenderer;
use Picqer\Barcode\Types\TypeCode128;

/**
 * Renders a Code 128 barcode as an inline SVG data URI.
 *
 * Inline so a label PDF carries its own barcode with nothing to lose track of,
 * and so a label reprinted next year looks identical to today's.
 */
final class BarcodeRenderer
{
    public function dataUri(string $value, int $widthPx = 520, int $heightPx = 60): string
    {
        $barcode = (new TypeCode128)->getBarcode($value);

        $svg = (new SvgRenderer)
            ->setForegroundColor([0, 0, 0])
            ->render($barcode, $widthPx, $heightPx);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
