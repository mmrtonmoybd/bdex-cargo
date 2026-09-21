<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves generated paperwork.
 *
 * Staff reach documents through the panel, which is already behind Shield.
 * Customers reach their own invoice through a signed link — signed rather than
 * guessable, so an invoice number cannot be incremented to read someone else's.
 */
final class DocumentDownloadController extends Controller
{
    /** Staff download, gated by the same permission that shows the document. */
    public function document(Request $request, Document $document): StreamedResponse
    {
        abort_unless($request->user()?->can('ViewAny:Order') ?? false, 403);

        return $this->stream($document->path, $document->title ?? 'document');
    }

    /** Customer download of their own invoice, via a signed URL. */
    public function invoice(Request $request, Invoice $invoice): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        if (blank($invoice->pdf_path)) {
            abort(404, 'That invoice has not been generated yet.');
        }

        return $this->stream($invoice->pdf_path, $invoice->invoice_number);
    }

    private function stream(string $path, string $name): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, str($name)->slug().'.pdf');
    }
}
