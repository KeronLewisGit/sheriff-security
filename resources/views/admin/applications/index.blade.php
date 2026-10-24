@php use App\Enums\ApplicationStatus; use App\Enums\Region; use App\Enums\DocumentType; @endphp
<x-layouts.admin title="Applications">
    <x-slot:actions>
        <a href="{{ route('admin.applications.pipeline', request()->query()) }}" class="a-btn a-btn-ghost"><x-icon name="columns" class="h-4 w-4" />Pipeline</a>
        <a href="{{ route('admin.applications.export', request()->query()) }}" class="a-btn a-btn-ghost"><x-icon name="download" class="h-4 w-4" />Export CSV</a>
    </x-slot:actions>
    <nav class="mb-4 flex flex-wrap gap-2" aria-label="Filter by status">
        <a href="{{ route('admin.applications.index', array_filter(['region' => $filters['region'], 'job' => $filters['job'], 'q' => $filters['q']])) }}" class="a-btn {{ ! $filters['status'] ? 'a-btn-primary' : 'a-btn-ghost' }}">All</a>
        @foreach (ApplicationStatus::cases() as $s)
            <a href="{{ route('admin.applications.index', array_filter(['status' => $s->value, 'region' => $filters['region'], 'job' => $filters['job'], 'q' => $filters['q']])) }}" class="a-btn {{ $filters['status'] === $s->value ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $s->label() }} <span class="num text-ink-3">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
    </nav>
    <form class="mb-4 flex flex-wrap gap-2" method="GET">
        @if ($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
        @if ($filters['q'])<input type="hidden" name="q" value="{{ $filters['q'] }}">@endif
        <label class="sr-only" for="job">Position</label>
        <select id="job" name="job" class="a-input max-w-full"><option value="">All positions</option><option value="general" @selected($filters['job'] === 'general')>General applications</option>@foreach ($jobs as $j)<option value="{{ $j->id }}" @selected($filters['job'] === (string) $j->id)>{{ $j->title }}</option>@endforeach</select>
        <label class="sr-only" for="region">Region</label>
        <select id="region" name="region" class="a-input max-w-full"><option value="">All regions</option>@foreach (Region::cases() as $r)<option value="{{ $r->value }}" @selected($filters['region'] === $r->value)>{{ $r->label() }}</option>@endforeach</select>
        <button class="a-btn a-btn-ghost">Filter</button>
    </form>
    <div class="a-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Applicant</th><th>Position</th><th>Region</th><th>Documents</th><th>Status</th><th class="r">Received</th></tr></thead>
            <tbody>
                @forelse ($applications as $a)
                    <tr>
                        <td><a class="font-bold hover:text-link" href="{{ route('admin.applications.show', $a) }}">{{ $a->full_name }}</a><span class="ref block text-xs text-ink-3">{{ $a->reference }}</span></td>
                        <td>{{ $a->jobPosting->title ?? 'General' }}</td>
                        <td>{{ $a->region->short() }}</td>
                        <td class="whitespace-nowrap text-sm">
                            <span @class(['text-good' => $a->hasDocument(DocumentType::PoliceClearance), 'text-ink-3' => ! $a->hasDocument(DocumentType::PoliceClearance)]) title="Police clearance">PCC</span> ·
                            <span @class(['text-good' => $a->hasDocument(DocumentType::Identification), 'text-ink-3' => ! $a->hasDocument(DocumentType::Identification)]) title="ID">ID</span> ·
                            <span class="text-ink-2">{{ $a->documents->count() }} files</span>
                        </td>
                        <td><x-status :status="$a->status" /></td>
                        <td class="r whitespace-nowrap text-ink-3">{{ $a->created_at->format('j M') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-ink-3">No applications match.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $applications->links() }}</div>
</x-layouts.admin>
