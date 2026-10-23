<?php

namespace App\Http\Controllers;

use App\Models\ComplianceDocument;
use App\Models\DocumentDownload;
use App\Models\VendorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The approved vendor pack. Routes sit behind the `signed` middleware, so an
 * edited or expired URL gets a 403 before reaching here; the token must also
 * still be live (not revoked, not past expires_at).
 */
class VendorPackController extends Controller
{
    public function show(string $token): View
    {
        $vendor = $this->live($token);

        return view('pages.vendor-pack', ['vendor' => $vendor->load('documents')]);
    }

    public function download(Request $request, string $token, int $document): StreamedResponse
    {
        $vendor = $this->live($token);
        /** @var ComplianceDocument|null $doc */
        $doc = $vendor->documents()->whereKey($document)->first();
        abort_unless($doc && $doc->hasFile() && Storage::disk('local')->exists((string) $doc->path), 404);

        DocumentDownload::create([
            'vendor_request_id' => $vendor->id,
            'compliance_document_id' => $doc->id,
            'document_title' => $doc->title,
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        $extension = pathinfo((string) $doc->path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download((string) $doc->path, 'Sheriff-Security-'.Str::slug($doc->title).'.'.$extension);
    }

    private function live(string $token): VendorRequest
    {
        $vendor = VendorRequest::where('access_token', $token)->first();
        abort_unless($vendor && $vendor->linkIsLive(), 410, 'This link has expired or been withdrawn.');

        return $vendor;
    }
}
