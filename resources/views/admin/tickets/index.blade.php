@php use App\Enums\TicketStatus; @endphp
<x-layouts.admin title="Alarm tickets">
    <nav class="mb-4 flex flex-wrap gap-2" aria-label="Filter by status">
        <a href="{{ route('admin.tickets.index') }}" class="a-btn {{ $status === 'open' ? 'a-btn-primary' : 'a-btn-ghost' }}">Open</a>
        @foreach (TicketStatus::cases() as $s)
            <a href="{{ route('admin.tickets.index', ['status' => $s->value]) }}" class="a-btn {{ $status === $s->value ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $s->label() }} <span class="num text-ink-3">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
        <a href="{{ route('admin.tickets.index', ['status' => 'all']) }}" class="a-btn {{ $status === 'all' ? 'a-btn-primary' : 'a-btn-ghost' }}">All</a>
    </nav>
    <div class="a-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Ticket</th><th>Customer</th><th>Issue</th><th>Status</th><th>Visit</th><th class="r">Logged</th></tr></thead>
            <tbody>
                @forelse ($tickets as $t)
                    <tr>
                        <td class="ref text-sm whitespace-nowrap"><a class="u-link" href="{{ route('admin.tickets.show', $t) }}">{{ $t->reference }}</a></td>
                        <td><a class="font-bold hover:text-link" href="{{ route('admin.tickets.show', $t) }}">{{ $t->name }}</a><span class="block max-w-60 truncate text-sm text-ink-3">{{ $t->site_address }}</span></td>
                        <td>{{ $t->issue->label() }}</td>
                        <td class="whitespace-nowrap"><x-status :status="$t->status" />@if ($t->urgent) <x-chip tone="hivis" icon="alert">Urgent</x-chip>@endif</td>
                        <td class="whitespace-nowrap">{{ $t->visit_on?->format('D j M') ?? '–' }}</td>
                        <td class="r whitespace-nowrap text-ink-3">{{ $t->created_at->format('j M, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-ink-3">No tickets here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tickets->links() }}</div>
</x-layouts.admin>
