@php use App\Enums\ProposalStatus; use App\Enums\Industry; @endphp
<x-layouts.admin title="Proposal requests">
    <x-slot:actions>
        <a href="{{ route('admin.proposals.board') }}" class="a-btn a-btn-ghost"><x-icon name="columns" class="h-4 w-4" />Board</a>
    </x-slot:actions>

    <nav class="mb-4 flex flex-wrap gap-2" aria-label="Filter by status">
        <a href="{{ route('admin.proposals.index') }}" class="a-btn {{ ! $filters['status'] && ! $filters['overdue'] ? 'a-btn-primary' : 'a-btn-ghost' }}">All</a>
        <a href="{{ route('admin.proposals.index', ['overdue' => 1]) }}" class="a-btn {{ $filters['overdue'] ? 'a-btn-primary' : 'a-btn-ghost' }}"><x-icon name="clock" class="h-4 w-4 text-hivis-text" />Overdue</a>
        @foreach (ProposalStatus::cases() as $s)
            <a href="{{ route('admin.proposals.index', ['status' => $s->value]) }}" class="a-btn {{ $filters['status'] === $s->value ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $s->label() }} <span class="num text-ink-3">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
    </nav>
    <form class="mb-4 flex flex-wrap gap-2" method="GET">
        @if ($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
        <label class="sr-only" for="industry">Industry</label>
        <select id="industry" name="industry" class="a-input" onchange="this.form.submit()">
            <option value="">All industries</option>
            @foreach (Industry::cases() as $i)<option value="{{ $i->value }}" @selected($filters['industry'] === $i->value)>{{ $i->label() }}</option>@endforeach
        </select>
        <noscript><button class="a-btn a-btn-ghost">Filter</button></noscript>
    </form>

    <div class="a-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Company</th><th>Site</th><th class="r">Posts</th><th>Armed</th><th>Status</th><th>Owner</th><th class="r">Received</th></tr></thead>
            <tbody>
                @forelse ($proposals as $p)
                    <tr>
                        <td class="ref text-sm"><a class="u-link" href="{{ route('admin.proposals.show', $p) }}">{{ $p->reference }}</a></td>
                        <td><a href="{{ route('admin.proposals.show', $p) }}" class="font-bold hover:text-link">{{ $p->company }}</a><span class="block text-sm text-ink-3">{{ $p->industry->label() }}</span></td>
                        <td>{{ $p->site_type->short() }}<span class="block max-w-56 truncate text-sm text-ink-3">{{ $p->site_location }}</span></td>
                        <td class="r">{{ $p->posts }}</td>
                        <td>{{ $p->armedLabel() }}</td>
                        <td class="whitespace-nowrap"><x-status :status="$p->status" />@if ($p->isOverdue()) <x-chip tone="hivis" icon="clock">Overdue</x-chip>@endif</td>
                        <td class="text-ink-2">{{ $p->owner->name ?? '–' }}</td>
                        <td class="r whitespace-nowrap text-ink-3">{{ $p->created_at->format('j M, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-10 text-center text-ink-3">No proposal requests match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $proposals->links() }}</div>
</x-layouts.admin>
