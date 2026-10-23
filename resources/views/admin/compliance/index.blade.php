<x-layouts.admin title="Compliance documents">
    <x-slot:actions><a href="{{ route('admin.compliance.create') }}" class="a-btn a-btn-primary"><x-icon name="plus" class="h-4 w-4" />Add document</a></x-slot:actions>
    <p class="mb-4 max-w-3xl text-ink-2">These are the documents procurement teams can request in the vendor pack. Files are stored privately and are only reachable through an approved, signed, expiring link.</p>
    <div class="a-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Document</th><th>Category</th><th>File</th><th>Valid to</th><th>Listed</th><th class="r">Downloads</th><th></th></tr></thead>
            <tbody>
                @forelse ($documents as $d)
                    <tr>
                        <td class="font-bold">{{ $d->title }}@if ($d->description)<span class="block text-sm font-normal text-ink-3">{{ $d->description }}</span>@endif</td>
                        <td>{{ $d->category->label() }}</td>
                        <td>@if ($d->hasFile())<a class="u-link" href="{{ route('admin.compliance.file', $d) }}">{{ \Illuminate\Support\Str::limit((string) $d->original_name, 28) }}</a>@else<x-chip tone="critical" icon="alert">Missing</x-chip>@endif</td>
                        <td class="whitespace-nowrap">@if ($d->expires_on)@if ($d->isExpired())<x-chip tone="critical" icon="clock">Expired {{ $d->expires_on->format('j M Y') }}</x-chip>@elseif ($d->expiresSoon())<x-chip tone="attention" icon="clock">{{ $d->expires_on->format('j M Y') }}</x-chip>@else{{ $d->expires_on->format('j M Y') }}@endif @else<span class="text-ink-3">–</span>@endif</td>
                        <td>{{ $d->is_listed ? 'Yes' : 'Hidden' }}</td>
                        <td class="r">{{ $d->downloads_count }}</td>
                        <td class="r"><a class="a-btn a-btn-ghost" href="{{ route('admin.compliance.edit', $d) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-ink-3">No documents yet. Add the ISO certificates first.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
