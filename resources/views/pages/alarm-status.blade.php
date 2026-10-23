@php use App\Enums\TicketStatus; $steps = [TicketStatus::Received, TicketStatus::Scheduled, TicketStatus::InProgress, TicketStatus::Resolved]; $at = array_search($ticket->status, $steps, true); @endphp
<x-layouts.site title="Ticket {{ $ticket->reference }}" :mobile-bar="false">
    <x-page-header title="Ticket status" tag="Sheriff Alarms" :no="$ticket->reference" :lead="$ticket->issue->label().' · logged '.$ticket->created_at->format('j M Y, g:i a')" />
    <section class="wrap mt-12 md:mt-16">
        <div class="max-w-2xl">
            <p class="flex flex-wrap items-center gap-3"><x-status :status="$ticket->status" class="!text-base" />@if ($ticket->urgent)<x-chip tone="hivis" icon="alert">Urgent</x-chip>@endif</p>
            @if ($ticket->status !== TicketStatus::Closed)
                <ol class="mt-8 grid gap-3 sm:grid-cols-4" aria-label="Progress">
                    @foreach ($steps as $i => $step)
                        @php($done = $at !== false && $i <= $at)
                        <li class="rounded-sm border-t-4 pt-2 {{ $done ? 'border-blue' : 'border-line' }}" @if ($i === $at) aria-current="step" @endif>
                            <span class="coord {{ $done ? 'text-link' : 'text-ink-3' }}">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="block font-bold {{ $done ? '' : 'text-ink-3' }}">{{ $step->label() }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
            <dl class="dl mt-8 rounded-md border border-line bg-surface p-5">
                @if ($ticket->visit_on)<dt>Technician visit</dt><dd>{{ $ticket->visit_on->format('l j F Y') }}</dd>@endif
                <dt>Site</dt><dd>{{ $ticket->site_address }}</dd>
                <dt>Last update</dt><dd>{{ ($ticket->status_changed_at ?? $ticket->created_at)->format('j M Y, g:i a') }}</dd>
                @if ($ticket->public_note)<dt>Note from Sheriff</dt><dd>{{ $ticket->public_note }}</dd>@endif
            </dl>
            <a href="{{ route('alarm-service') }}" class="btn btn-outline mt-8">Report another fault</a>
        </div>
    </section>
</x-layouts.site>
