@props(['title', 'actions' => null])
@php
    /** @var \App\Models\User $me */
    $me = auth()->user();
    $sales = $me->role->can('sales');
    $hiring = $me->role->can('hiring');
    $sections = [];
    $sections[] = [null, [['Overview', 'admin.dashboard', 'admin.dashboard', 'grid', null, false]]];
    if ($sales) {
        $sections[] = ['Business development', [
            ['Proposals', 'admin.proposals.index', 'admin.proposals.*', 'file', \App\Models\ProposalRequest::overdue()->count(), true],
            ['Vendor packs', 'admin.vendor.index', 'admin.vendor.*', 'clipboard', \App\Models\VendorRequest::where('status', 'pending')->count(), false],
            ['Compliance documents', 'admin.compliance.index', 'admin.compliance.*', 'shield', null, false],
            ['Alarm tickets', 'admin.tickets.index', 'admin.tickets.*', 'bell', \App\Models\ServiceTicket::open()->count(), false],
            ['Messages', 'admin.messages.index', 'admin.messages.*', 'inbox', \App\Models\Message::open()->count(), false],
        ]];
    }
    if ($hiring) {
        $sections[] = ['Recruitment', [
            ['Applications', 'admin.applications.index', ['admin.applications.index', 'admin.applications.show'], 'users', \App\Models\JobApplication::where('status', 'new')->count(), false],
            ['Pipeline', 'admin.applications.pipeline', 'admin.applications.pipeline', 'columns', null, false],
            ['Jobs', 'admin.jobs.index', 'admin.jobs.*', 'briefcase', null, false],
        ]];
    }
    if ($me->role->can('admin')) {
        $sections[] = ['Administration', [
            ['Users & roles', 'admin.users.index', 'admin.users.*', 'users', null, false],
            ['Settings', 'admin.settings.edit', 'admin.settings.*', 'settings', null, false],
        ]];
    }
    $search = $sales ? ['admin.proposals.index', 'Search proposals: company, reference, site'] : ($hiring ? ['admin.applications.index', 'Search applicants: name, phone, reference'] : null);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>{{ $title }} · Sheriff staff</title>
    <meta name="robots" content="noindex">
</head>
<body class="admin min-h-screen bg-bg text-ink" x-data="adminShell" @keydown.escape.window="nav = false">
    <a href="#content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-surface focus:px-4 focus:py-3">Skip to content</a>

    <div class="fixed inset-0 z-30 bg-black/40 lg:hidden" style="display:none" x-show="nav" x-transition.opacity @click="nav = false"></div>
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col overflow-y-auto bg-brand-950 transition-transform lg:translate-x-0" :class="nav && 'translate-x-0'" aria-label="Dashboard">
        <a href="{{ route('admin.dashboard') }}" class="flex h-16 flex-none items-center px-5"><x-logo tone="light" /></a>
        <nav class="admin-nav flex-1 px-3 pb-4">
            @foreach ($sections as [$heading, $items])
                @if ($heading)<h2>{{ $heading }}</h2>@endif
                <div class="space-y-0.5">
                    @foreach ($items as [$label, $route, $match, $icon, $badge, $alarm])
                        <a href="{{ route($route) }}" @if(request()->routeIs($match)) aria-current="page" @endif>
                            <x-icon :name="$icon" class="h-[18px] w-[18px]" />{{ $label }}
                            @if ($badge)<span @class(['num ml-auto rounded-full px-2 py-0.5 text-xs font-bold', 'bg-hivis text-hivis-ink' => $alarm, 'bg-sky text-brand-950' => ! $alarm]) aria-label="{{ $badge }} {{ $alarm ? 'overdue' : 'waiting' }}">{{ $badge }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="admin-nav space-y-1 border-t border-white/10 px-3 py-4">
            <a href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="external" class="h-[18px] w-[18px]" />View website</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="flex min-h-11 w-full items-center gap-3 rounded-[3px] px-[0.85rem] text-left font-medium text-white/80 hover:bg-white/5 hover:text-white"><x-icon name="log-out" class="h-[18px] w-[18px]" />Sign out</button>
            </form>
            <p class="truncate px-3 pt-2 text-xs text-white/60">{{ $me->name }} · {{ $me->role->label() }}</p>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 border-b border-line bg-bg/90 backdrop-blur">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
                <button type="button" class="a-btn a-btn-ghost w-11 px-0 lg:hidden" @click="nav = true" aria-label="Open navigation"><x-icon name="menu" /></button>
                @if ($search)
                    <form action="{{ route($search[0]) }}" method="GET" role="search" class="relative max-w-md flex-1">
                        <label for="admin-search" class="sr-only">{{ $search[1] }}</label>
                        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-3" />
                        <input id="admin-search" data-search name="q" value="{{ input_string(request('q')) }}" class="a-input w-full pl-9 pr-10" placeholder="{{ $search[1] }}" autocomplete="off">
                        <span class="kbd absolute right-2.5 top-1/2 hidden -translate-y-1/2 sm:inline" aria-hidden="true">/</span>
                    </form>
                @endif
                <div class="ml-auto flex items-center gap-1">
                    <button type="button" class="a-btn w-11 px-0 text-ink-2 hover:bg-surface-2" @click="toggleDensity()" :aria-pressed="(density === 'compact').toString()" aria-label="Compact rows"><x-icon name="rows" /></button>
                    <button type="button" class="a-btn w-11 px-0 text-ink-2 hover:bg-surface-2" onclick="toggleTheme()" aria-label="Switch light or dark theme"><x-icon name="moon" class="dark-hide h-5 w-5" /><x-icon name="sun" class="dark-show h-5 w-5" /></button>
                </div>
            </div>
        </header>

        <main id="content" class="px-4 py-6 sm:px-6 lg:py-8">
            <div class="mb-5 flex min-h-11 flex-wrap items-center justify-between gap-3">
                <h1 class="font-display text-2xl font-semibold">{{ $title }}</h1>
                @if ($actions)<div class="flex flex-wrap gap-2">{{ $actions }}</div>@endif
            </div>
            @if (session('status'))
                <div class="mb-5 flex items-center gap-2.5 rounded-md border border-line bg-surface px-5 py-4 font-medium" role="status"><x-icon name="check-circle" class="h-5 w-5 flex-none text-good" />{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-md border border-line bg-surface px-5 py-4" role="alert">
                    <p class="flex items-center gap-2 font-bold"><x-icon name="alert" class="h-5 w-5 text-[color:var(--critical)]" />Please fix the following</p>
                    <ul class="mt-1 list-disc pl-11">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
