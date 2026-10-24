<?php

namespace App\Http\Controllers;

use App\Enums\Industry;
use App\Enums\ProposalStatus;
use App\Enums\SiteType;
use App\Models\ProposalRequest;
use App\Notifications\NewSubmission;
use App\Notifications\VisitorNotice;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProposalController extends Controller
{
    public function create(Request $request): View
    {
        $services = (array) sheriff('services');
        $service = input_string($request->query('service'));

        return view('pages.rfp', ['preset' => $services[$service]['title'] ?? null]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            $request->session()->put('rfp_reference', 'RFP-'.now()->format('y').'-00000');

            return to_route('rfp.thanks');
        }

        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'industry' => ['required', Rule::enum(Industry::class)],
            'site_type' => ['required', Rule::enum(SiteType::class)],
            'site_location' => ['required', 'string', 'max:255'],
            'posts' => ['required', 'integer', 'min:1', 'max:999'],
            'coverage' => ['required', Rule::in(array_keys(ProposalRequest::COVERAGE))],
            'armed' => ['required', Rule::in(array_keys(ProposalRequest::ARMED))],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'contract_term' => ['nullable', Rule::in(array_keys(ProposalRequest::TERMS))],
            'details' => ['nullable', 'string', 'max:5000'],
            'documents' => ['nullable', 'array', 'max:5'],
            'documents.*' => ['file', 'max:10240', 'mimes:'.implode(',', ProposalRequest::FILE_TYPES)],
        ], [
            'phone.regex' => 'Enter a phone number we can call.',
            'documents.*.max' => 'Each document must be 10 MB or smaller.',
            'documents.*.mimes' => 'Documents can be PDF, Word, Excel, images or a ZIP.',
            'documents.max' => 'Attach up to 5 documents.',
            'posts.min' => 'Enter at least one post.',
        ]);

        $proposal = DB::transaction(function () use ($request, $data) {
            $proposal = ProposalRequest::create(Arr::except($data, ['documents']) + ['status' => ProposalStatus::New]);
            foreach ((array) $request->file('documents', []) as $upload) {
                /** @var UploadedFile $upload */
                $proposal->documents()->create([
                    'path' => $upload->store('proposals/'.$proposal->id, 'local'),
                    'original_name' => mb_substr($upload->getClientOriginalName(), 0, 200),
                    'size' => (int) $upload->getSize(),
                ]);
            }

            return $proposal;
        });

        Notify::office(new NewSubmission(
            "RFP {$proposal->reference}: {$proposal->company} · {$proposal->posts} post(s), {$proposal->site_type->short()}",
            [
                $proposal->contact_name.($proposal->job_title ? ", {$proposal->job_title}" : ''),
                $proposal->industry->label().' · '.$proposal->site_location,
                $proposal->coverageLabel().' · '.$proposal->armedLabel(),
                'Target: respond within '.ProposalRequest::responseHours().' hours.',
            ],
            route('admin.proposals.show', $proposal),
            $proposal->email,
            $proposal->contact_name,
        ));
        Notify::visitor($proposal->email, new VisitorNotice(
            "We have your request for proposal ({$proposal->reference})",
            'Hello '.$proposal->contact_name.',',
            [
                'Thank you for asking Sheriff Security Services to quote for '.$proposal->company.'. Your reference is '.$proposal->reference.'.',
                'Our business development team aims to respond within '.ProposalRequest::responseHours().' hours, usually to arrange a site survey.',
            ],
        ));

        $request->session()->put('rfp_reference', $proposal->reference);

        return to_route('rfp.thanks');
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->get('rfp_reference');

        return $reference ? view('pages.rfp-thanks', ['reference' => $reference]) : to_route('rfp.create');
    }
}
