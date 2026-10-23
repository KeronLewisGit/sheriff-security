<x-layouts.admin title="Messages">
    <nav class="mb-4 flex gap-2"><a href="{{ route('admin.messages.index') }}" class="a-btn {{ ! $all ? 'a-btn-primary' : 'a-btn-ghost' }}">Open</a><a href="{{ route('admin.messages.index', ['show' => 'all']) }}" class="a-btn {{ $all ? 'a-btn-primary' : 'a-btn-ghost' }}">All</a></nav>
    <ul class="space-y-3">
        @forelse ($messages as $m)
            <li class="a-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-bold">{{ $m->name }}@if ($m->company) <span class="font-normal text-ink-2">· {{ $m->company }}</span>@endif</p>
                        <p class="text-sm text-ink-3">{{ $m->topicLabel() }} · {{ $m->created_at->format('j M Y, H:i') }} · <a class="u-link" href="mailto:{{ $m->email }}">{{ $m->email }}</a>@if ($m->phone) · {{ $m->phone }}@endif</p>
                    </div>
                    <form method="POST" action="{{ route('admin.messages.update', $m) }}">@csrf @method('PATCH')<input type="hidden" name="handled" value="{{ $m->handled_at ? 0 : 1 }}"><button class="a-btn a-btn-ghost">{{ $m->handled_at ? 'Reopen' : 'Mark handled' }}</button></form>
                </div>
                <p class="mt-3 whitespace-pre-line">{{ $m->body }}</p>
            </li>
        @empty
            <li class="a-card p-10 text-center text-ink-3">Nothing waiting.</li>
        @endforelse
    </ul>
    <div class="mt-4">{{ $messages->links() }}</div>
</x-layouts.admin>
