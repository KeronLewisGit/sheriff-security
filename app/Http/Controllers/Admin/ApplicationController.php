<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\Region;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.applications.index', [
            'applications' => JobApplication::with(['jobPosting', 'documents'])->filter($filters)->latest()->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $filters,
            'counts' => JobApplication::filter(array_merge($filters, ['status' => null]))->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'jobs' => JobPosting::orderBy('title')->get(['id', 'title']),
        ]);
    }

    /** Kanban: open stages as columns; hired and rejected are counted, not shown. */
    public function pipeline(Request $request): View
    {
        $filters = array_merge($this->filters($request), ['status' => null]);
        $open = JobApplication::with(['jobPosting', 'documents'])->filter($filters)
            ->whereIn('status', array_map(fn (ApplicationStatus $s) => $s->value, array_filter(ApplicationStatus::cases(), fn (ApplicationStatus $s) => $s->isOpen())))
            ->latest('status_changed_at')->latest()->get();

        return view('admin.applications.pipeline', [
            'columns' => collect(ApplicationStatus::cases())->filter(fn (ApplicationStatus $s) => $s->isOpen())
                ->mapWithKeys(fn (ApplicationStatus $s) => [$s->value => $open->where('status', $s)->values()]),
            'closed' => JobApplication::filter($filters)->whereIn('status', ['hired', 'rejected'])->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'filters' => $filters,
            'jobs' => JobPosting::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function show(JobApplication $application): View
    {
        return view('admin.applications.show', ['application' => $application->load(['jobPosting', 'documents'])]);
    }

    public function update(Request $request, JobApplication $application): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(ApplicationStatus::class)],
            'staff_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);
        if (isset($data['status'])) {
            $application->moveTo(ApplicationStatus::from($data['status']));
        }
        if (array_key_exists('staff_notes', $data)) {
            $application->staff_notes = $data['staff_notes'];
        }
        $application->save();

        if ($request->wantsJson()) {
            return response()->json(['label' => $application->status->label(), 'status' => $application->status->value]);
        }

        return back()->with('status', "{$application->reference} saved.");
    }

    /** Documents are private: streamed to signed-in HR staff only, never linked publicly. */
    public function document(JobApplication $application, ApplicationDocument $document): StreamedResponse
    {
        abort_unless($document->job_application_id === $application->id && Storage::disk('local')->exists($document->path), 404);
        $extension = pathinfo($document->path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($document->path, Str::slug($application->full_name.'-'.$document->type->label()).'-'.$application->reference.'.'.$extension);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = JobApplication::with(['jobPosting', 'documents'])->filter($this->filters($request));

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // byte-order mark so Excel reads accents correctly
            fputcsv($out, ['Reference', 'Received', 'Status', 'First name', 'Last name', 'Phone', 'Email', 'Region', 'Job', 'Experience', 'Firearm licence', 'Offshore ready', 'Police clearance', 'ID', 'Certificates', 'CV'], escape: '');
            foreach ($query->lazyByIdDesc(500) as $a) {
                /** @var JobApplication $a */
                $count = fn (string $type) => $a->documents->where('type.value', $type)->count();
                fputcsv($out, array_map(self::csvSafe(...), [
                    $a->reference, $a->created_at->format('Y-m-d H:i'), $a->status->label(), $a->first_name, $a->last_name,
                    $a->phone, $a->email, $a->region->short(), $a->jobPosting->title ?? 'General application', $a->experience->label(),
                    $a->armed_licence ? 'Yes' : 'No', $a->offshore_ready ? 'Yes' : 'No',
                    $count('police_clearance') ? 'Yes' : 'No', $count('identification') ? 'Yes' : 'No', $count('certificate'), $count('cv') ? 'Yes' : 'No',
                ]), escape: '');
            }
            fclose($out);
        }, 'sheriff-applications-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Text starting with = + - @ would run as a spreadsheet formula, so it is prefixed with a quote. */
    private static function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /** @return array<string, string|null> */
    private function filters(Request $request): array
    {
        $status = ApplicationStatus::tryFrom(input_string($request->query('status')))?->value;
        $region = Region::tryFrom(input_string($request->query('region')))?->value;
        $jobInput = input_string($request->query('job'));
        $job = $jobInput === 'general' || ctype_digit($jobInput) ? $jobInput : null;
        $q = input_string($request->query('q')) ?: null;

        return compact('status', 'region', 'job', 'q');
    }
}
