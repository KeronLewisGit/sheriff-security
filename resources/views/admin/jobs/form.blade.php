@php use App\Enums\EmploymentType; use App\Enums\JobStatus; use App\Models\JobPosting; @endphp
<x-layouts.admin :title="$job->exists ? 'Edit: '.$job->title : 'New job posting'">
    <form method="POST" action="{{ $job->exists ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}" class="a-card max-w-3xl space-y-4 p-5">
        @csrf @if ($job->exists) @method('PUT') @endif
        <div><label class="a-label" for="title">Title</label><input id="title" name="title" class="a-input w-full" value="{{ old('title', $job->title) }}" required></div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div><label class="a-label" for="department">Department</label><select id="department" name="department" class="a-input w-full">@foreach (JobPosting::DEPARTMENTS as $d)<option @selected(old('department', $job->department) === $d)>{{ $d }}</option>@endforeach</select></div>
            <div><label class="a-label" for="location">Location</label><input id="location" name="location" class="a-input w-full" value="{{ old('location', $job->location) }}" required></div>
            <div><label class="a-label" for="employment_type">Type</label><select id="employment_type" name="employment_type" class="a-input w-full">@foreach (EmploymentType::cases() as $t)<option value="{{ $t->value }}" @selected(old('employment_type', $job->employment_type?->value) === $t->value)>{{ $t->label() }}</option>@endforeach</select></div>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div><label class="a-label" for="status">Status</label><select id="status" name="status" class="a-input w-full">@foreach (JobStatus::cases() as $s)<option value="{{ $s->value }}" @selected(old('status', $job->status?->value) === $s->value)>{{ $s->label() }}</option>@endforeach</select></div>
            <div><label class="a-label" for="openings">Openings</label><input id="openings" name="openings" type="number" min="1" class="a-input w-full" value="{{ old('openings', $job->openings) }}"></div>
            <div><label class="a-label" for="closes_on">Closes</label><input id="closes_on" name="closes_on" type="date" class="a-input w-full" value="{{ old('closes_on', $job->closes_on?->toDateString()) }}"></div>
        </div>
        <div><label class="a-label" for="summary">Summary</label><textarea id="summary" name="summary" rows="3" class="a-input w-full" required>{{ old('summary', $job->summary) }}</textarea></div>
        <div><label class="a-label" for="duties">Duties <span class="font-normal text-ink-3">(one per line)</span></label><textarea id="duties" name="duties" rows="6" class="a-input w-full">{{ old('duties', $job->duties) }}</textarea></div>
        <div><label class="a-label" for="requirements">Requirements <span class="font-normal text-ink-3">(one per line)</span></label><textarea id="requirements" name="requirements" rows="6" class="a-input w-full">{{ old('requirements', $job->requirements) }}</textarea></div>
        <div class="flex gap-2 pt-2"><button class="a-btn a-btn-primary">Save</button><a class="a-btn a-btn-ghost" href="{{ route('admin.jobs.index') }}">Cancel</a></div>
    </form>
    @if ($job->exists)
        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" class="mt-4" onsubmit="return confirm('Delete this posting?')">@csrf @method('DELETE')<button class="a-btn a-btn-danger">Delete posting</button></form>
    @endif
</x-layouts.admin>
