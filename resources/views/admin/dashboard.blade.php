<x-layouts.admin title="Overview">
    @if ($notifyMissing || $notifyInvalid)
        <div class="hivis-note mb-5 text-sm" role="note">
            <p class="font-bold">Email alerts need attention</p>
            <p>@if ($notifyMissing)Nobody is emailed about new submissions yet.@else These notification addresses were skipped: {{ implode(', ', $notifyInvalid) }}.@endif
                @if (auth()->user()->role->can('admin'))<a class="u-link" href="{{ route('admin.settings.edit') }}">Set notification emails</a>@endif</p>
        </div>
    @endif

    @if ($sales)
        {{-- The one number: proposal requests not answered within the response target --}}
        @php($n = $overdue->count())
        <section @class(['a-card overflow-hidden', '!border-hivis' => $n > 0]) aria-labelledby="lead-number">
            <div class="grid gap-6 p-6 md:grid-cols-[auto_1fr] md:items-center md:p-8">
                <div class="flex items-center gap-5">
                    <span @class(['font-display text-7xl font-bold leading-none num', 'text-hivis-text' => $n > 0, 'text-good' => $n === 0])>{{ $n }}</span>
                    <div>
                        <h2 id="lead-number" class="font-display text-xl font-semibold">{{ $n === 1 ? 'proposal request' : 'proposal requests' }} not answered within {{ $responseHours }} hours</h2>
                        <p class="mt-1 text-ink-2">
                            @if ($n > 0)
                                Oldest waiting {{ $overdue->first()->created_at->diffForHumans(short: true, parts: 2, syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}. Move each to Qualified (or Lost) once you have called them.
                            @else
                                All caught up. Every request has been picked up inside the target.
                            @endif
                        </p>
                    </div>
                </div>
                @if ($n > 0)
                    <a href="{{ route('admin.proposals.index', ['overdue' => 1]) }}" class="a-btn a-btn-primary md:justify-self-end">Work the overdue list<x-icon name="arrow-right" class="h-4 w-4" /></a>
                @endif
            </div>
            @if ($n > 0)
                <ul class="divide-y divide-line border-t border-line">
                    @foreach ($overdue->take(5) as $p)
                        <li><a href="{{ route('admin.proposals.show', $p) }}" class="flex flex-wrap items-center gap-x-4 gap-y-1 px-6 py-3 hover:bg-surface-2 md:px-8">
                            <span class="ref text-sm text-ink-3">{{ $p->reference }}</span>
                            <span class="font-bold">{{ $p->company }}</span>
                            <span class="text-ink-2">{{ $p->posts }} {{ \Illuminate\Support\Str::plural('post', $p->posts) }} · {{ $p->site_type->short() }} · {{ $p->industry->label() }}</span>
                            <x-chip tone="hivis" icon="clock" class="ml-auto">{{ $p->created_at->diffForHumans(short: true, syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }} waiting</x-chip>
                        </a></li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['New proposal requests', $newProposals, 'Status “New”, including those still inside the target', route('admin.proposals.index', ['status' => 'new'])],
                ['Vendor packs awaiting approval', $vendorPending, 'Nothing is sent until you approve', route('admin.vendor.index', ['status' => 'pending'])],
                ['Open alarm tickets', $ticketsOpen, $ticketsUrgent ? $ticketsUrgent.' marked urgent' : 'None marked urgent', route('admin.tickets.index')],
                ['Unread messages', $messagesOpen, 'From the contact form', route('admin.messages.index')],
            ] as [$label, $value, $note, $href])
                <a href="{{ $href }}" class="a-card block p-5 hover:shadow-md">
                    <p class="text-sm text-ink-2">{{ $label }}</p>
                    <p class="mt-2 text-[2rem] font-bold leading-none num">{{ number_format($value) }}</p>
                    <p class="mt-3 text-sm text-ink-3">{{ $note }}</p>
                </a>
            @endforeach
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[1.4fr_1fr]">
            <section class="a-card p-5" aria-labelledby="weekly" x-data="columnChart">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 id="weekly" class="font-bold">Proposal requests per week</h2>
                    <p class="text-sm text-ink-3">@if ($onTimeRate !== null){{ $onTimeRate }}% of {{ $answeredCount }} answered within target, last 90 days @else No answered requests in the last 90 days @endif</p>
                </div>
                <div class="relative mt-6 flex h-44 items-end gap-2 border-b border-line" aria-hidden="true">
                    @foreach ($weeks as $w)
                        <button type="button" tabindex="-1" class="group flex h-full flex-1 items-end" data-label="Week of {{ $w['start']->format('j M') }}" data-value="{{ $w['count'] }} {{ \Illuminate\Support\Str::plural('request', $w['count']) }}" @mouseenter="show($el)" @mouseleave="hide()">
                            <span class="block w-full rounded-t-[4px] bg-series group-hover:opacity-80" style="height: {{ max(2, round($w['count'] / $weeksMax * 100)) }}%"></span>
                        </button>
                    @endforeach
                    <div x-show="tip" x-cloak class="pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-full rounded bg-brand-950 px-2.5 py-1.5 text-xs text-white shadow-md" :style="tip && `left:${tip.x}px; top:${tip.y - 6}px`"><span x-text="tip?.label"></span>: <strong x-text="tip?.value"></strong></div>
                </div>
                <div class="mt-2 flex gap-2 text-xs text-ink-3" aria-hidden="true">@foreach ($weeks as $w)<span class="flex-1 text-center">{{ $w['start']->format('j M') }}</span>@endforeach</div>
                <table class="sr-only"><caption>Proposal requests per week</caption><thead><tr><th>Week of</th><th>Requests</th></tr></thead><tbody>@foreach ($weeks as $w)<tr><td>{{ $w['start']->format('j M Y') }}</td><td>{{ $w['count'] }}</td></tr>@endforeach</tbody></table>
            </section>

            <section class="a-card p-5" aria-labelledby="pipe">
                <h2 id="pipe" class="font-bold">Proposal pipeline</h2>
                <ul class="mt-4 space-y-3">
                    @php($pmax = max(1, $pipeline->max('count')))
                    @foreach ($pipeline as $row)
                        <li>
                            <a href="{{ route('admin.proposals.index', ['status' => $row['status']->value]) }}" class="grid grid-cols-[8.5rem_1fr_2.5rem] items-center gap-3 hover:text-link">
                                <span class="text-sm">{{ $row['status']->label() }}</span>
                                <span class="h-2.5 rounded-full bg-series-track"><span class="block h-full rounded-full bg-series" style="width: {{ round($row['count'] / $pmax * 100) }}%"></span></span>
                                <span class="r num text-right font-bold">{{ $row['count'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.proposals.board') }}" class="a-btn a-btn-ghost mt-5"><x-icon name="columns" class="h-4 w-4" />Open the board</a>
            </section>
        </div>

        @if ($docsExpiring->isNotEmpty() || $docsMissingFile)
            <section class="a-card mt-5 p-5" aria-labelledby="docs">
                <h2 id="docs" class="flex items-center gap-2 font-bold"><x-icon name="shield" class="h-5 w-5 text-hivis-text" />Vendor-pack documents to update</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($docsExpiring as $d)
                        <li class="flex flex-wrap items-center gap-2"><x-chip :tone="$d->isExpired() ? 'critical' : 'attention'" icon="clock">{{ $d->isExpired() ? 'Expired' : 'Expires' }} {{ $d->expires_on?->format('j M Y') }}</x-chip><a class="u-link" href="{{ route('admin.compliance.edit', $d) }}">{{ $d->title }}</a></li>
                    @endforeach
                    @if ($docsMissingFile)<li>{{ $docsMissingFile }} listed {{ \Illuminate\Support\Str::plural('document', $docsMissingFile) }} {{ $docsMissingFile === 1 ? 'has' : 'have' }} no file uploaded. <a class="u-link" href="{{ route('admin.compliance.index') }}">Upload</a></li>@endif
                </ul>
            </section>
        @endif
    @endif

    @if ($hiring)
        <section @class(['mt-8' => $sales]) aria-labelledby="hr">
            @if ($sales)<h2 id="hr" class="font-display text-xl font-semibold">Recruitment</h2>@endif
            <div @class(['a-card mt-3 grid gap-6 p-6 md:grid-cols-[auto_1fr_auto] md:items-center', '!border-hivis' => $staleApplications > 0])>
                <span @class(['font-display font-bold leading-none num', 'text-7xl' => ! $sales, 'text-5xl' => $sales, 'text-hivis-text' => $staleApplications > 0, 'text-good' => $staleApplications === 0])>{{ $staleApplications }}</span>
                <div>
                    <p @unless ($sales) id="hr" @endunless class="font-display text-xl font-semibold">{{ $staleApplications === 1 ? 'application' : 'applications' }} waiting more than 2 days</p>
                    <p class="mt-1 text-ink-2">@if ($oldestStale)Oldest received {{ $oldestStale->format('j M') }}. @endif{{ $newApplications }} new in total · {{ $openJobs }} open {{ \Illuminate\Support\Str::plural('posting', $openJobs) }}.</p>
                </div>
                <a href="{{ route('admin.applications.pipeline') }}" class="a-btn a-btn-primary"><x-icon name="columns" class="h-4 w-4" />Pipeline</a>
            </div>
            <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_1.4fr]">
                <div class="a-card p-5">
                    <h3 class="font-bold">Recruitment pipeline</h3>
                    @php($hmax = max(1, $hiringPipeline->max('count')))
                    <ul class="mt-4 space-y-3">
                        @foreach ($hiringPipeline as $row)
                            <li><a href="{{ route('admin.applications.index', ['status' => $row['status']->value]) }}" class="grid grid-cols-[6.5rem_1fr_2.5rem] items-center gap-3 hover:text-link"><span class="text-sm">{{ $row['status']->label() }}</span><span class="h-2.5 rounded-full bg-series-track"><span class="block h-full rounded-full bg-series" style="width: {{ round($row['count'] / $hmax * 100) }}%"></span></span><span class="num text-right font-bold">{{ $row['count'] }}</span></a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="a-card overflow-x-auto">
                    <h3 class="px-5 pt-5 font-bold">Latest applications</h3>
                    <table class="data-table mt-3">
                        <thead><tr><th>Applicant</th><th>Position</th><th>Status</th><th class="r">Received</th></tr></thead>
                        <tbody>
                            @forelse ($recentApplications as $a)
                                <tr><td><a class="font-bold hover:text-link" href="{{ route('admin.applications.show', $a) }}">{{ $a->full_name }}</a></td><td class="text-ink-2">{{ $a->jobPosting->title ?? 'General' }}</td><td><x-status :status="$a->status" /></td><td class="r text-ink-3">{{ $a->created_at->format('j M') }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="text-ink-3">No applications yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</x-layouts.admin>
