@php use App\Enums\VendorStatus; $free = (bool) preg_match('/@(gmail|yahoo|hotmail|outlook|live|icloud|aol)\./i', $vr->email); @endphp
<x-layouts.admin :title="'Vendor pack · '.$vr->company">
    <x-slot:actions><a href="{{ route('admin.vendor.index') }}" class="a-btn a-btn-ghost"><x-icon name="arrow-left" class="h-4 w-4" />All requests</a></x-slot:actions>
    <div class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
        <div class="min-w-0 space-y-5">
            <section class="a-card p-5">
                <p class="flex flex-wrap items-center gap-2"><span class="ref text-ink-3">{{ $vr->reference }}</span><x-status :status="$vr->status" /></p>
                <dl class="dl mt-5">
                    <dt>Organisation</dt><dd>{{ $vr->company }}</dd>
                    <dt>Requested by</dt><dd>{{ $vr->contact_name }}@if ($vr->job_title), {{ $vr->job_title }}@endif</dd>
                    <dt>Email</dt><dd><a class="u-link" href="mailto:{{ $vr->email }}">{{ $vr->email }}</a>@if ($free) <x-chip tone="attention" icon="alert">Free-mail address: verify</x-chip>@endif</dd>
                    @if ($vr->phone)<dt>Phone</dt><dd>{{ $vr->phone }}</dd>@endif
                    <dt>Purpose</dt><dd>{{ $vr->purposeLabel() }}</dd>
                    @if ($vr->tender_reference)<dt>Tender / registration</dt><dd>{{ $vr->tender_reference }}</dd>@endif
                    <dt>Received</dt><dd>{{ $vr->created_at->format('j M Y, H:i') }}</dd>
                    @if ($vr->decided_at)<dt>Decision</dt><dd>{{ $vr->status->label() }} by {{ $vr->decider->name ?? 'staff' }}, {{ $vr->decided_at->format('j M Y, H:i') }}@if ($vr->decision_note) · “{{ $vr->decision_note }}”@endif</dd>@endif
                    @if ($vr->expires_at && $vr->status === VendorStatus::Approved)<dt>Link expires</dt><dd>{{ $vr->expires_at->format('j M Y, H:i') }} {{ $vr->expires_at->isPast() ? '(expired)' : '' }}</dd>@endif
                </dl>
                @if ($vr->message)<p class="mt-5 whitespace-pre-line rounded bg-surface-2 p-4">{{ $vr->message }}</p>@endif
            </section>
            <section class="a-card p-5">
                <h2 class="font-bold">Documents requested</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($vr->documents as $d)
                        <li class="flex flex-wrap items-center gap-2">{{ $d->title }}
                            @if (! $d->hasFile())<x-chip tone="critical" icon="alert">No file uploaded</x-chip>@endif
                            @if ($d->isExpired())<x-chip tone="critical" icon="clock">Expired</x-chip>@elseif ($d->expiresSoon())<x-chip tone="attention" icon="clock">Expires {{ $d->expires_on?->format('j M') }}</x-chip>@endif
                        </li>
                    @endforeach
                </ul>
            </section>
            <section class="a-card overflow-x-auto">
                <h2 class="px-5 pt-5 font-bold">Download log</h2>
                <table class="data-table mt-3">
                    <thead><tr><th>When</th><th>Document</th><th>IP address</th><th>Browser</th></tr></thead>
                    <tbody>
                        @forelse ($vr->downloads as $dl)
                            <tr><td class="whitespace-nowrap">{{ $dl->created_at->format('j M Y, H:i') }}</td><td class="min-w-56">{{ $dl->document_title }}</td><td class="ref text-sm">{{ $dl->ip }}</td><td class="max-w-64 truncate text-sm text-ink-3">{{ $dl->user_agent }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-ink-3">No downloads yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>

        <aside class="min-w-0 space-y-5">
            @if ($vr->status === VendorStatus::Pending)
                <form method="POST" action="{{ route('admin.vendor.approve', $vr) }}" class="a-card space-y-3 p-5">
                    @csrf
                    <h2 class="font-bold">Approve and send</h2>
                    <p class="text-sm text-ink-2">Emails {{ $vr->email }} a signed link to the {{ $vr->documents->count() }} {{ \Illuminate\Support\Str::plural('document', $vr->documents->count()) }} above. It works for {{ setting('vendor_link_days', '7') }} days and every download is logged.</p>
                    @if ($vr->documents->contains(fn ($d) => ! $d->hasFile()))
                        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="allow_missing" value="1" class="mt-1">Send anyway; documents without a file show as “being prepared”.</label>
                    @endif
                    <button class="a-btn a-btn-blue w-full"><x-icon name="check" class="h-4 w-4" />Approve and email link</button>
                </form>
                <form method="POST" action="{{ route('admin.vendor.decline', $vr) }}" class="a-card space-y-3 p-5">
                    @csrf
                    <h2 class="font-bold">Decline</h2>
                    <label class="a-label" for="decision_note">Internal reason <span class="font-normal text-ink-3">(not emailed)</span></label>
                    <input id="decision_note" name="decision_note" class="a-input w-full" maxlength="255">
                    <button class="a-btn a-btn-danger w-full">Decline request</button>
                </form>
            @elseif ($vr->linkIsLive())
                <div class="a-card space-y-3 p-5">
                    <h2 class="font-bold">Live link</h2>
                    <p class="text-sm text-ink-2">Expires {{ $vr->expires_at?->diffForHumans() }}. Copy it if the requester lost the email.</p>
                    <input readonly class="a-input w-full font-mono text-xs" value="{{ $vr->packUrl() }}" aria-label="Signed vendor pack link" onclick="this.select()">
                    <form method="POST" action="{{ route('admin.vendor.revoke', $vr) }}">@csrf<button class="a-btn a-btn-danger w-full"><x-icon name="lock" class="h-4 w-4" />Withdraw link now</button></form>
                </div>
            @else
                <div class="a-card p-5 text-sm text-ink-2">This request is closed. If the organisation needs the documents again, ask them to submit a new request so it is logged.</div>
            @endif
        </aside>
    </div>
</x-layouts.admin>
