<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    public function index(): View
    {
        return view('admin.jobs.index', ['jobs' => JobPosting::withCount('applications')->orderByRaw("case status when 'open' then 0 when 'draft' then 1 else 2 end")->orderBy('title')->get()]);
    }

    public function create(): View
    {
        return view('admin.jobs.form', ['job' => new JobPosting(['status' => JobStatus::Draft, 'employment_type' => EmploymentType::FullTime, 'openings' => 1, 'location' => 'Georgetown', 'department' => 'Operations'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $job = new JobPosting;
        $this->save($request, $job);

        return to_route('admin.jobs.index')->with('status', "“{$job->title}” created.");
    }

    public function edit(JobPosting $job): View
    {
        return view('admin.jobs.form', ['job' => $job]);
    }

    public function update(Request $request, JobPosting $job): RedirectResponse
    {
        $this->save($request, $job);

        return to_route('admin.jobs.index')->with('status', "“{$job->title}” saved.");
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        $job->delete();

        return to_route('admin.jobs.index')->with('status', 'Posting deleted. Its applications were kept as general applications.');
    }

    private function save(Request $request, JobPosting $job): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'department' => ['required', Rule::in(JobPosting::DEPARTMENTS)],
            'location' => ['required', 'string', 'max:120'],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'status' => ['required', Rule::enum(JobStatus::class)],
            'openings' => ['required', 'integer', 'min:1', 'max:500'],
            'summary' => ['required', 'string', 'max:1000'],
            'duties' => ['nullable', 'string', 'max:4000'],
            'requirements' => ['nullable', 'string', 'max:4000'],
            'closes_on' => ['nullable', 'date'],
        ]);
        $job->fill($data);
        if (! $job->exists) {
            $base = Str::slug($data['title']) ?: 'job';
            $slug = $base;
            for ($i = 2; JobPosting::where('slug', $slug)->exists(); $i++) {
                $slug = $base.'-'.$i;
            }
            $job->slug = $slug;
        }
        $job->save();
    }
}
