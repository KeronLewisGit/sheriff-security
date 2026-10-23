@php use App\Enums\ApplicationStatus; @endphp
<x-layouts.admin :title="$application->full_name">
    <x-slot:actions><a href="{{ route('admin.applications.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />All applications</a></x-slot:actions>
    <div class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
        <div class="space-y-5">
            <section class="a-card p-5">
                <p class="flex flex-wrap items-center gap-2"><span class="ref text-ink-3">{{ $application->reference }}</span><x-status :status="$application->status" /></p>
                <dl class="dl mt-5">
                    <dt>Position</dt><dd>{{ $application->jobPosting->title ?? 'General application' }}</dd>
                    <dt>Phone</dt><dd><a class="u-link" href="{{ tel_href($application->phone) }}">{{ $application->phone }}</a></dd>
                    <dt>Email</dt><dd>{{ $application->email ?? '–' }}</dd>
                    <dt>Region</dt><dd>{{ $application->region->label() }}</dd>
                    <dt>Experience</dt><dd>{{ $application->experience->label() }}</dd>
                    <dt>Firearm licence</dt><dd>{{ $application->armed_licence ? 'Yes' : 'No' }}</dd>
                    <dt>Rotations away</dt><dd>{{ $application->offshore_ready ? 'Yes' : 'No' }}</dd>
                    <dt>Received</dt><dd>{{ $application->created_at->format('j M Y, H:i') }}</dd>
                </dl>
                @if ($application->message)<p class="mt-5 whitespace-pre-line rounded bg-surface-2 p-4">{{ $application->message }}</p>@endif
            </section>
            <section class="a-card p-5">
                <h2 class="font-bold">Documents</h2>
                <ul class="mt-3 space-y-1">
                    @forelse ($application->documents as $d)
                        <li><a class="inline-flex min-h-11 items-center gap-2 text-link" href="{{ route('admin.applications.document', [$application, $d]) }}"><x-icon name="download" class="h-4 w-4" /><strong>{{ $d->type->label() }}</strong> {{ $d->original_name }} <span class="text-ink-3">{{ human_bytes($d->size) }}</span></a></li>
                    @empty
                        <li class="text-ink-3">No documents.</li>
                    @endforelse
                </ul>
            </section>
        </div>
        <form method="POST" action="{{ route('admin.applications.update', $application) }}" class="a-card h-max space-y-4 p-5">
            @csrf @method('PATCH')
            <h2 class="font-bold">Decision</h2>
            <div><label class="a-label" for="status">Stage</label><select id="status" name="status" class="a-input w-full">@foreach (ApplicationStatus::cases() as $s)<option value="{{ $s->value }}" @selected($application->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
            <div><label class="a-label" for="staff_notes">HR notes</label><textarea id="staff_notes" name="staff_notes" rows="6" class="a-input w-full">{{ old('staff_notes', $application->staff_notes) }}</textarea></div>
            <button class="a-btn a-btn-primary w-full">Save</button>
        </form>
    </div>
</x-layouts.admin>
