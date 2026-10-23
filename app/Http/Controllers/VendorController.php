<?php

namespace App\Http\Controllers;

use App\Enums\VendorStatus;
use App\Models\ComplianceDocument;
use App\Models\VendorRequest;
use App\Notifications\NewSubmission;
use App\Notifications\VisitorNotice;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function create(): View
    {
        return view('pages.vendor', ['documents' => ComplianceDocument::listed()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            $request->session()->put('vendor_reference', 'VP-'.now()->format('y').'-00000');

            return to_route('vendor.thanks');
        }

        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            // A work address helps staff verify the requester; free-mail is allowed but flagged in the admin.
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'purpose' => ['required', Rule::in(array_keys(VendorRequest::PURPOSES))],
            'tender_reference' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['integer', Rule::exists('compliance_documents', 'id')->where('is_listed', true)],
            'confirm' => ['accepted'],
        ], [
            'documents.required' => 'Choose at least one document.',
            'confirm.accepted' => 'Please confirm the documents are for your organisation’s procurement use only.',
        ]);

        $vendor = VendorRequest::create(Arr::except($data, ['documents', 'confirm']) + ['status' => VendorStatus::Pending]);
        $vendor->documents()->sync(array_unique(array_map('intval', $data['documents'])));

        Notify::office(new NewSubmission(
            "Vendor pack request {$vendor->reference}: {$vendor->company}",
            [
                $vendor->contact_name.($vendor->job_title ? ", {$vendor->job_title}" : '').' <'.$vendor->email.'>',
                $vendor->purposeLabel().($vendor->tender_reference ? ' · '.$vendor->tender_reference : ''),
                'Requested: '.$vendor->documents->pluck('title')->implode(', '),
                'Nothing is sent until someone approves it in the dashboard.',
            ],
            route('admin.vendor.show', $vendor),
        ));
        Notify::visitor($vendor->email, new VisitorNotice(
            "Vendor pack request received ({$vendor->reference})",
            'Hello '.$vendor->contact_name.',',
            [
                'We have your request for Sheriff Security Services’ compliance documents on behalf of '.$vendor->company.'.',
                'A member of our team checks every request. Once approved you will receive a private download link that works for '.setting('vendor_link_days', '7').' days.',
            ],
        ));

        $request->session()->put('vendor_reference', $vendor->reference);

        return to_route('vendor.thanks');
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->get('vendor_reference');

        return $reference ? view('pages.vendor-thanks', ['reference' => $reference]) : to_route('vendor.create');
    }
}
