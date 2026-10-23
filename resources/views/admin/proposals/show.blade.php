@php use App\Enums\ProposalStatus; use App\Models\ProposalRequest; @endphp
<x-layouts.admin :title="$proposal->company">
    <x-slot:actions><a href="{{ route('admin.proposals.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />All requests</a></x-slot:actions>
    <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
        <div class="space-y-5">
            <section class="a-card p-5">
                <p class="flex flex-wrap items-center gap-2"><span class="ref text-ink-3">{{ $proposal->reference }}</span><x-status :status="$proposal->status" />@if ($proposal->isOverdue())<x-chip tone="hivis" icon="clock">Past {{ ProposalRequest::responseHours() }}h target</x-chip>@endif</p>
                <dl class="dl mt-5">
                    <dt>Contact</dt><dd>{{ $proposal->contact_name }}@if ($proposal->job_title), {{ $proposal->job_title }}@endif</dd>
                    <dt>Email</dt><dd><a class="u-link" href="mailto:{{ $proposal->email }}?subject={{ rawurlencode('Your request for proposal '.$proposal->reference) }}">{{ $proposal->email }}</a></dd>
                    <dt>Phone</dt><dd><a class="u-link" href="{{ tel_href($proposal->phone) }}">{{ $proposal->phone }}</a></dd>
                    <dt>Industry</dt><dd>{{ $proposal->industry->label() }}</dd>
                    <dt>Site type</dt><dd>{{ $proposal->site_type->label() }}</dd>
                    <dt>Location</dt><dd>{{ $proposal->site_location }}</dd>
                    <dt>Posts</dt><dd>{{ $proposal->posts }} · {{ $proposal->coverageLabel() }}</dd>
                    <dt>Armed</dt><dd>{{ $proposal->armedLabel() }}</dd>
                    <dt>Start</dt><dd>{{ $proposal->start_date?->format('j M Y') ?? 'Not given' }}</dd>
                    <dt>Term</dt><dd>{{ ProposalRequest::TERMS[$proposal->contract_term] ?? 'Not given' }}</dd>
                    <dt>Received</dt><dd>{{ $proposal->created_at->format('j M Y, H:i') }}</dd>
                    <dt>First response</dt><dd>{{ $proposal->responded_at ? $proposal->responded_at->format('j M Y, H:i').' ('.$proposal->created_at->diffForHumans($proposal->responded_at, syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE).')' : 'Not yet' }}</dd>
                </dl>
                @if ($proposal->details)<div class="mt-5 whitespace-pre-line rounded bg-surface-2 p-4">{{ $proposal->details }}</div>@endif
            </section>
            <section class="a-card p-5">
                <h2 class="font-bold">Documents</h2>
                <ul class="mt-3 space-y-2">
                    @forelse ($proposal->documents as $d)
                        <li><a class="inline-flex min-h-11 items-center gap-2 text-link" href="{{ route('admin.proposals.document', [$proposal, $d]) }}"><x-icon name="download" class="h-4 w-4" />{{ $d->original_name }} <span class="text-ink-3">{{ human_bytes($d->size) }}</span></a></li>
                    @empty
                        <li class="text-ink-3">None attached.</li>
                    @endforelse
                </ul>
            </section>
        </div>
        <form method="POST" action="{{ route('admin.proposals.update', $proposal) }}" class="a-card h-max space-y-4 p-5">
            @csrf @method('PATCH')
            <h2 class="font-bold">Pipeline</h2>
            <div><label class="a-label" for="status">Stage</label><select id="status" name="status" class="a-input w-full">@foreach (ProposalStatus::cases() as $s)<option value="{{ $s->value }}" @selected($proposal->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
            <div><label class="a-label" for="owner_id">Owner</label><select id="owner_id" name="owner_id" class="a-input w-full"><option value="">Unassigned</option>@foreach ($staff as $u)<option value="{{ $u->id }}" @selected($proposal->owner_id === $u->id)>{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="a-label" for="value_estimate">Estimated monthly value (GYD)</label><input id="value_estimate" name="value_estimate" type="number" step="0.01" min="0" class="a-input w-full" value="{{ old('value_estimate', $proposal->value_estimate) }}"></div>
            <div><label class="a-label" for="staff_notes">Internal notes</label><textarea id="staff_notes" name="staff_notes" rows="6" class="a-input w-full">{{ old('staff_notes', $proposal->staff_notes) }}</textarea></div>
            <button class="a-btn a-btn-primary w-full">Save</button>
        </form>
    </div>
</x-layouts.admin>
