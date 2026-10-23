<x-layouts.admin title="Proposal board">
    <x-slot:actions><a href="{{ route('admin.proposals.index') }}" class="a-btn a-btn-ghost"><x-icon name="list" class="h-4 w-4" />List</a></x-slot:actions>
    <div x-data="pipeline" class="relative">
        <div x-show="toast" x-cloak x-transition class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded bg-brand-950 px-4 py-2.5 text-sm font-medium text-white shadow-md" role="status" x-text="toast"></div>
        <p class="mb-4 text-sm text-ink-3">Drag a card to move it. Moving out of “New” records the first response. Cards with an orange edge are past the {{ \App\Models\ProposalRequest::responseHours() }}-hour target. Without drag and drop, open a request and change its status there.</p>
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach ($columns as $stage => $items)
                @php($status = \App\Enums\ProposalStatus::from($stage))
                <section class="kanban-col" data-stage="{{ $stage }}" :class="over === '{{ $stage }}' && 'drag-over'" @dragover.prevent="over = '{{ $stage }}'" @dragleave.self="over = null" @drop.prevent="drop('{{ $stage }}')" aria-label="{{ $status->label() }}">
                    <header class="flex items-center justify-between px-1 pb-2"><x-status :status="$status" /><span class="num rounded-full bg-surface px-2 text-xs font-bold" data-count>{{ $items->count() }}</span></header>
                    <div class="flex min-h-[50vh] flex-col gap-2" data-list>
                        <p class="rounded border border-dashed border-line-strong p-4 text-center text-xs text-ink-3 {{ $items->count() ? 'hidden' : '' }}" data-empty>Drop here</p>
                        @foreach ($items as $p)
                            <article id="card-{{ $p->id }}" class="kanban-card" draggable="true" data-overdue="{{ $p->isOverdue() ? 'true' : 'false' }}" data-url="{{ route('admin.proposals.update', $p) }}" data-name="{{ $p->company }}" @dragstart="start($event, {{ $p->id }})" @dragend="end()">
                                <a href="{{ route('admin.proposals.show', $p) }}" class="font-bold hover:text-link">{{ $p->company }}</a>
                                <p class="ref mt-0.5 text-xs text-ink-3">{{ $p->reference }}</p>
                                <p class="mt-1.5 text-sm text-ink-2">{{ $p->posts }} {{ \Illuminate\Support\Str::plural('post', $p->posts) }} · {{ $p->site_type->short() }} · {{ $p->armedLabel() }}</p>
                                <p class="mt-1 flex justify-between text-xs text-ink-3"><span>{{ $p->owner->name ?? 'Unassigned' }}</span><span>{{ $p->created_at->diffForHumans(short: true) }}</span></p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
