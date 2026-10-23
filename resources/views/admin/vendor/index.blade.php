@php use App\Enums\VendorStatus; @endphp
<x-layouts.admin title="Vendor pack requests">
    <x-slot:actions><a href="{{ route('admin.compliance.index') }}" class="a-btn a-btn-ghost"><x-icon name="shield" class="h-4 w-4" />Compliance documents</a></x-slot:actions>
    <nav class="mb-4 flex flex-wrap gap-2" aria-label="Filter by status">
        <a href="{{ route('admin.vendor.index') }}" class="a-btn {{ ! $status ? 'a-btn-primary' : 'a-btn-ghost' }}">All</a>
        @foreach (VendorStatus::cases() as $s)
            <a href="{{ route('admin.vendor.index', ['status' => $s->value]) }}" class="a-btn {{ $status === $s->value ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $s->label() }} <span class="num text-ink-3">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
    </nav>
    <div class="a-card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Reference</th><th>Organisation</th><th>Requested by</th><th>Purpose</th><th class="r">Docs</th><th>Status</th><th>Link</th><th class="r">Downloads</th><th class="r">Received</th></tr></thead>
            <tbody>
                @forelse ($requests as $r)
                    <tr>
                        <td class="ref text-sm"><a class="u-link" href="{{ route('admin.vendor.show', $r) }}">{{ $r->reference }}</a></td>
                        <td><a href="{{ route('admin.vendor.show', $r) }}" class="font-bold hover:text-link">{{ $r->company }}</a></td>
                        <td>{{ $r->contact_name }}<span class="block text-sm text-ink-3">{{ $r->email }}</span></td>
                        <td class="text-ink-2">{{ \Illuminate\Support\Str::before($r->purposeLabel(), ' /') }}</td>
                        <td class="r">{{ $r->documents_count }}</td>
                        <td><x-status :status="$r->status" /></td>
                        <td class="whitespace-nowrap text-sm">@if ($r->linkIsLive())<span class="text-ink-2">Until {{ $r->expires_at?->format('j M') }}</span>@elseif ($r->status === VendorStatus::Approved)<span class="text-ink-3">Expired</span>@else<span class="text-ink-3">–</span>@endif</td>
                        <td class="r">{{ $r->downloads_count }}</td>
                        <td class="r whitespace-nowrap text-ink-3">{{ $r->created_at->format('j M') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-10 text-center text-ink-3">No requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
</x-layouts.admin>
