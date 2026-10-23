@php use App\Enums\TicketStatus; @endphp
<x-layouts.admin :title="'Ticket '.$ticket->reference">
    <x-slot:actions><a href="{{ route('admin.tickets.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />All tickets</a></x-slot:actions>
    <div class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
        <section class="a-card p-5">
            <p class="flex flex-wrap items-center gap-2"><x-status :status="$ticket->status" />@if ($ticket->urgent)<x-chip tone="hivis" icon="alert">Urgent</x-chip>@endif</p>
            <dl class="dl mt-5">
                <dt>Issue</dt><dd>{{ $ticket->issue->label() }}</dd>
                <dt>Customer</dt><dd>{{ $ticket->name }}</dd>
                <dt>Phone</dt><dd><a class="u-link" href="{{ tel_href($ticket->phone) }}">{{ $ticket->phone }}</a></dd>
                <dt>Email</dt><dd><a class="u-link" href="mailto:{{ $ticket->email }}?subject={{ rawurlencode('Alarm ticket '.$ticket->reference) }}">{{ $ticket->email }}</a></dd>
                <dt>Account</dt><dd>{{ $ticket->account_number ?: 'Not given' }}</dd>
                <dt>Site</dt><dd>{{ $ticket->site_address }}</dd>
                <dt>Best time</dt><dd>{{ \App\Models\ServiceTicket::TIMES[$ticket->preferred_time] ?? 'Any time' }}</dd>
                <dt>Logged</dt><dd>{{ $ticket->created_at->format('j M Y, H:i') }}</dd>
                @if ($ticket->resolved_at)<dt>Resolved</dt><dd>{{ $ticket->resolved_at->format('j M Y, H:i') }}</dd>@endif
            </dl>
            <p class="mt-5 whitespace-pre-line rounded bg-surface-2 p-4">{{ $ticket->description }}</p>
        </section>
        <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}" class="a-card h-max space-y-4 p-5">
            @csrf @method('PATCH')
            <h2 class="font-bold">Update</h2>
            <div><label class="a-label" for="status">Status</label><select id="status" name="status" class="a-input w-full">@foreach (TicketStatus::cases() as $s)<option value="{{ $s->value }}" @selected($ticket->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
            <div><label class="a-label" for="visit_on">Technician visit</label><input id="visit_on" name="visit_on" type="date" class="a-input w-full" value="{{ old('visit_on', $ticket->visit_on?->toDateString()) }}"></div>
            <div><label class="a-label" for="public_note">Note for the customer <span class="font-normal text-ink-3">(shown on the status page)</span></label><input id="public_note" name="public_note" class="a-input w-full" maxlength="255" value="{{ old('public_note', $ticket->public_note) }}"></div>
            <div><label class="a-label" for="staff_notes">Internal notes</label><textarea id="staff_notes" name="staff_notes" rows="5" class="a-input w-full">{{ old('staff_notes', $ticket->staff_notes) }}</textarea></div>
            <button class="a-btn a-btn-primary w-full">Save</button>
        </form>
    </div>
</x-layouts.admin>
