@php use App\Enums\ApplicationStatus; use App\Enums\DocumentType; @endphp
<x-layouts.admin title="Recruitment pipeline">
    <x-slot:actions>
        <form class="flex gap-2" method="GET"><label class="sr-only" for="job">Position</label><select id="job" name="job" class="a-input w-56 max-w-[60vw]" onchange="this.form.submit()"><option value="">All positions</option><option value="general" @selected($filters['job'] === 'general')>General applications</option>@foreach ($jobs as $j)<option value="{{ $j->id }}" @selected($filters['job'] === (string) $j->id)>{{ $j->title }}</option>@endforeach</select><noscript><button class="a-btn a-btn-ghost">Go</button></noscript></form>
        <a href="{{ route('admin.applications.index') }}" class="a-btn a-btn-ghost"><x-icon name="list" class="h-4 w-4" />List</a>
    </x-slot:actions>
    <div x-data="pipeline" class="relative">
        <div x-show="toast" x-cloak x-transition class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded bg-brand-950 px-4 py-2.5 text-sm font-medium text-white shadow-md" role="status" x-text="toast"></div>
        <p class="mb-4 text-sm text-ink-3">Drag a card to move an applicant. Hired: <a class="u-link" href="{{ route('admin.applications.index', ['status' => 'hired']) }}">{{ $closed['hired'] ?? 0 }}</a> · Rejected: <a class="u-link" href="{{ route('admin.applications.index', ['status' => 'rejected']) }}">{{ $closed['rejected'] ?? 0 }}</a>. To hire or reject, open the applicant.</p>
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach ($columns as $stage => $apps)
                @php($status = ApplicationStatus::from($stage))
                <section class="kanban-col" data-stage="{{ $stage }}" :class="over === '{{ $stage }}' && 'drag-over'" @dragover.prevent="over = '{{ $stage }}'" @dragleave.self="over = null" @drop.prevent="drop('{{ $stage }}')" aria-label="{{ $status->label() }}">
                    <header class="flex items-center justify-between px-1 pb-2"><x-status :status="$status" /><span class="num rounded-full bg-surface px-2 text-xs font-bold" data-count>{{ $apps->count() }}</span></header>
                    <div class="flex min-h-[55vh] flex-col gap-2" data-list>
                        <p class="rounded border border-dashed border-line-strong p-4 text-center text-xs text-ink-3 {{ $apps->count() ? 'hidden' : '' }}" data-empty>Drop here</p>
                        @foreach ($apps as $a)
                            <article id="card-{{ $a->id }}" class="kanban-card" draggable="true" data-url="{{ route('admin.applications.update', $a) }}" data-name="{{ $a->full_name }}" @dragstart="start($event, {{ $a->id }})" @dragend="end()">
                                <a href="{{ route('admin.applications.show', $a) }}" class="font-bold hover:text-link">{{ $a->full_name }}</a>
                                <p class="mt-0.5 text-xs text-ink-3">{{ $a->jobPosting->title ?? 'General application' }}</p>
                                <p class="mt-2 flex flex-wrap gap-1 text-[11px]">
                                    <span class="chip !py-0">{{ $a->region->short() }}</span>
                                    @if ($a->hasDocument(DocumentType::PoliceClearance))<span class="chip !py-0" data-tone="good"><x-icon name="check" />PCC</span>@endif
                                    @if ($a->armed_licence)<span class="chip !py-0">Firearm</span>@endif
                                </p>
                                <p class="mt-1.5 text-right text-[11px] text-ink-3">{{ ($a->status_changed_at ?? $a->created_at)->diffForHumans(short: true) }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
