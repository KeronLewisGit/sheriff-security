<x-layouts.admin title="Jobs">
    <x-slot:actions><a href="{{ route('admin.jobs.create') }}" class="a-btn a-btn-primary"><x-icon name="plus" class="h-4 w-4" />New posting</a></x-slot:actions>
    <div class="a-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Department</th><th>Status</th><th class="r">Openings</th><th class="r">Applications</th><th>Closes</th><th></th></tr></thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td class="font-bold">{{ $job->title }}<span class="block text-sm font-normal text-ink-3">{{ $job->location }}</span></td>
                        <td>{{ $job->department }}</td>
                        <td><x-chip :tone="$job->status->tone()" icon="dot">{{ $job->status->label() }}</x-chip></td>
                        <td class="r">{{ $job->openings }}</td>
                        <td class="r"><a class="u-link" href="{{ route('admin.applications.index', ['job' => $job->id]) }}">{{ $job->applications_count }}</a></td>
                        <td>{{ $job->closes_on?->format('j M Y') ?? '–' }}</td>
                        <td class="r whitespace-nowrap"><a class="a-btn a-btn-ghost" href="{{ route('admin.jobs.edit', $job) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-ink-3">No postings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
