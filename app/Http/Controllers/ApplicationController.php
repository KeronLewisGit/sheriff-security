<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\Experience;
use App\Enums\Region;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Notifications\NewSubmission;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public const int MAX_KB = 5120;

    public function create(Request $request): View
    {
        $jobs = JobPosting::open()->orderBy('title')->get();

        return view('pages.careers.apply', ['jobs' => $jobs, 'selected' => $jobs->firstWhere('slug', input_string($request->query('job')))]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            $request->session()->put('application_reference', 'APP-'.now()->format('y').'-00000');

            return to_route('apply.thanks');
        }

        $file = ['nullable', 'file', 'max:'.self::MAX_KB, 'mimes:'.implode(',', JobApplication::FILE_TYPES)];
        $data = $request->validate([
            'job_posting_id' => ['nullable', 'integer', Rule::exists('job_postings', 'id')->where('status', 'open')],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'region' => ['required', Rule::enum(Region::class)],
            'experience' => ['required', Rule::enum(Experience::class)],
            'armed_licence' => ['boolean'],
            'offshore_ready' => ['boolean'],
            'message' => ['nullable', 'string', 'max:2000'],
            'police_clearance' => ['required', ...array_slice($file, 1)],
            'identification' => ['required', ...array_slice($file, 1)],
            'certificate' => ['nullable', 'array', 'max:4'],
            'certificate.*' => array_slice($file, 1),
            'cv' => $file,
            'consent' => ['accepted'],
        ], [
            'phone.regex' => 'Enter a phone number we can call, e.g. 600 1234.',
            'police_clearance.required' => 'Upload your police clearance. A clear photo of it is fine.',
            'identification.required' => 'Upload your national ID card or passport.',
            // Named per file field: a bare '*.max' would also relabel name and message length errors.
            ...array_fill_keys(['police_clearance.max', 'identification.max', 'certificate.*.max', 'cv.max'], 'Each file must be 5 MB or smaller.'),
            'certificate.max' => 'Attach up to 4 certificates.',
            'consent.accepted' => 'Please confirm we may keep your documents for this application.',
        ]);

        $application = DB::transaction(function () use ($request, $data) {
            $application = JobApplication::create([
                ...Arr::only($data, ['job_posting_id', 'first_name', 'last_name', 'phone', 'email', 'region', 'experience', 'message']),
                'armed_licence' => $request->boolean('armed_licence'),
                'offshore_ready' => $request->boolean('offshore_ready'),
                'status' => ApplicationStatus::New,
            ]);

            $uploads = [
                DocumentType::PoliceClearance->value => [$request->file('police_clearance')],
                DocumentType::Identification->value => [$request->file('identification')],
                DocumentType::Certificate->value => (array) $request->file('certificate', []),
                DocumentType::Cv->value => [$request->file('cv')],
            ];
            foreach ($uploads as $type => $files) {
                foreach (array_filter($files) as $upload) {
                    /** @var UploadedFile $upload */
                    $application->documents()->create([
                        'type' => $type,
                        // Random name on the private disk; the original name is only shown to staff.
                        'path' => $upload->store('applications/'.$application->id, 'local'),
                        'original_name' => mb_substr($upload->getClientOriginalName(), 0, 200),
                        'size' => (int) $upload->getSize(),
                    ]);
                }
            }

            return $application;
        });

        Notify::office(new NewSubmission(
            'New application: '.$application->full_name.' ('.($application->jobPosting->title ?? 'general').')',
            [$application->reference.' · '.$application->region->label(), $application->experience->label()],
            route('admin.applications.show', $application),
        ));

        $request->session()->put('application_reference', $application->reference);

        return to_route('apply.thanks');
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->get('application_reference');

        return $reference ? view('pages.careers.thanks', ['reference' => $reference]) : to_route('apply.create');
    }
}
