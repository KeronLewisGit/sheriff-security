@props(['title' => null, 'description' => null, 'jsonLd' => [], 'mobileBar' => true, 'ogImage' => 'fpso-sunset'])
@php
    $name = sheriff('name');
    $pageTitle = $title ? $title.' · '.$name : $name.' · Oil & gas, marine and commercial security in Guyana';
    $metaDescription = $description ?? 'ISO 9001:2015 and ISO 45001:2018 certified security for Guyana’s energy, maritime and commercial sectors: guarding, marine and air patrol, cash in transit and alarm monitoring.';
    $phone = setting('phone');
    $email = setting('email');
    $nav = [
        ['route' => 'services', 'match' => ['services', 'services.*'], 'label' => 'Services'],
        ['route' => 'industries', 'match' => 'industries', 'label' => 'Industries'],
        ['route' => 'hse', 'match' => ['hse', 'vendor.*'], 'label' => 'HSE & compliance'],
        ['route' => 'group', 'match' => ['group', 'leadership'], 'label' => 'Company'],
        ['route' => 'careers', 'match' => ['careers', 'careers.*', 'apply.*'], 'label' => 'Careers'],
        ['route' => 'contact', 'match' => 'contact', 'label' => 'Contact'],
    ];
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'SecurityService',
        'name' => $name,
        'legalName' => sheriff('legal_name'),
        'url' => route('home'),
        'telephone' => $phone ? '+'.(gy_digits($phone) ?? '') : null,
        'email' => $email ?: null,
        'foundingDate' => (string) sheriff('founded'),
        'logo' => asset('favicon.svg'),
        'image' => asset('img/fpso-sunset-1920.webp'),
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('address_line'), 'addressLocality' => setting('address_city'), 'addressCountry' => 'GY'],
        'areaServed' => 'Guyana',
        'sameAs' => array_values(array_filter([setting('facebook'), setting('instagram'), setting('linkedin')])),
    ]);
@endphp
<!DOCTYPE html>
<html lang="en-GY">
<head>
    @include('partials.head')
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if (sheriff('preview'))
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $name }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/'.$ogImage.'-1920.webp') }}">
    <meta name="twitter:card" content="summary_large_image">
    @foreach (array_merge([$organization], $jsonLd) as $ld)
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach
</head>
<body class="min-h-screen {{ $mobileBar ? 'pb-20 lg:pb-0' : '' }}" x-data="{ menu: false }" @keydown.escape.window="menu = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-surface focus:px-4 focus:py-3 focus:shadow-md">Skip to content</a>

    @if (sheriff('preview'))
        <div class="bg-surface-2 text-sm text-ink-2">
            <p class="wrap flex items-start gap-2 py-2"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" /><span><strong class="font-bold text-ink">Concept preview.</strong> A proposed website for Sheriff Security Services, built from public sources. Not the company’s official site.</span></p>
        </div>
    @endif

    <header class="sticky top-0 z-40 bg-brand-950 text-white">
        {{-- Utility strip: the things procurement and alarm customers come for --}}
        <div class="hidden border-b border-white/10 lg:block">
            <div class="wrap flex h-10 items-center gap-6 text-sm">
                <p class="coord flex items-center gap-2 text-sky"><x-icon name="shield" class="h-4 w-4" />ISO 9001:2015 · ISO 45001:2018</p>
                <nav class="ml-auto flex items-center gap-5 text-white/80" aria-label="Customer services">
                    <a href="{{ route('vendor.create') }}" class="inline-flex min-h-11 items-center hover:text-white">Vendor prequalification pack</a>
                    <a href="{{ route('alarm-service') }}" class="inline-flex min-h-11 items-center hover:text-white">Alarm service &amp; fault report</a>
                    @if ($phone)<a href="{{ tel_href($phone) }}" class="inline-flex min-h-11 items-center gap-1.5 font-bold text-white"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($phone) }}</a>@endif
                </nav>
            </div>
        </div>
        <div class="wrap flex h-[4.5rem] items-center gap-2 min-[400px]:gap-3">
            <a href="{{ route('home') }}" class="mr-auto flex-none rounded" aria-label="{{ $name }}, home"><x-logo tone="light" /></a>

            <nav class="hidden items-center lg:flex" aria-label="Main">
                @foreach ($nav as $item)
                    @php($current = request()->routeIs($item['match']))
                    <a href="{{ route($item['route']) }}" @class(['relative whitespace-nowrap px-2.5 py-2.5 font-medium transition-colors xl:px-3.5', 'text-white' => $current, 'text-white/78 hover:text-white' => ! $current]) @if($current) aria-current="page" @endif>
                        {{ $item['label'] }}
                        @if ($current)<span class="absolute inset-x-2.5 -bottom-[0.95rem] h-[3px] bg-sky xl:inset-x-3.5" aria-hidden="true"></span>@endif
                    </a>
                @endforeach
            </nav>

            <button type="button" onclick="toggleTheme()" class="hidden h-11 w-11 flex-none items-center justify-center rounded text-white/75 hover:bg-white/10 hover:text-white sm:inline-flex" aria-label="Switch light or dark theme">
                <x-icon name="moon" class="h-5 w-5 dark-hide" /><x-icon name="sun" class="hidden h-5 w-5 dark-show" />
            </button>
            <a href="{{ route('rfp.create') }}" class="btn btn-blue btn-sm hidden flex-none whitespace-nowrap lg:inline-flex">Request a proposal<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <button type="button" class="inline-flex h-11 w-11 flex-none items-center justify-center rounded ring-1 ring-white/25 lg:hidden" @click="menu = !menu" :aria-expanded="menu.toString()" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="x" x-show="menu" x-cloak />
            </button>
        </div>
        <div id="mobile-menu" class="max-h-[calc(100vh-4.5rem)] overflow-y-auto border-t border-white/10 bg-brand-950 lg:hidden" x-show="menu" x-cloak x-transition.opacity>
            <nav class="wrap flex flex-col py-3" aria-label="Mobile">
                <a href="{{ route('home') }}" class="font-display rounded px-1 py-2.5 text-2xl font-semibold">Home</a>
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" class="font-display rounded px-1 py-2.5 text-2xl font-semibold" @if(request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <div class="mt-3 grid gap-2 border-t border-white/10 pt-4 text-white/85">
                    <a href="{{ route('vendor.create') }}" class="flex min-h-11 items-center gap-2"><x-icon name="clipboard" class="h-5 w-5 text-sky" />Vendor prequalification pack</a>
                    <a href="{{ route('alarm-service') }}" class="flex min-h-11 items-center gap-2"><x-icon name="bell" class="h-5 w-5 text-sky" />Alarm service &amp; fault report</a>
                    <a href="{{ route('leadership') }}" class="flex min-h-11 items-center gap-2"><x-icon name="users" class="h-5 w-5 text-sky" />Leadership</a>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('rfp.create') }}" class="btn btn-blue">Request a proposal</a>
                    @if ($phone)<a href="{{ tel_href($phone) }}" class="btn btn-on-dark"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($phone) }}</a>@endif
                </div>
                <button type="button" onclick="toggleTheme()" class="mt-3 flex min-h-11 items-center gap-2 rounded px-1 text-left font-medium text-white/75"><x-icon name="moon" class="h-5 w-5" />Switch light or dark theme</button>
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="relative isolate mt-24 bg-brand-950 text-white/78">
        <div class="graticule absolute inset-0 -z-10" aria-hidden="true"></div>
        <div class="wrap grid gap-10 py-16 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            <div>
                <x-logo tone="light" />
                <p class="mt-5 max-w-xs">Security for Guyana’s energy, maritime and commercial sectors since {{ sheriff('founded') }}. Part of the {{ sheriff('group_name') }} group.</p>
                @if ($phone)<a href="{{ tel_href($phone) }}" class="font-display mt-6 inline-flex min-h-12 items-center gap-2 text-2xl font-semibold text-white hover:text-sky"><x-icon name="phone" class="h-5 w-5" />{{ format_phone($phone) }}</a>@endif
            </div>
            <div>
                <h2 class="ptw text-white/60">Services</h2>
                <ul class="mt-4 space-y-1">
                    @foreach (sheriff('services') as $slug => $s)
                        <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('services.show', $slug) }}">{{ $s['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2 class="ptw text-white/60">Procurement</h2>
                <ul class="mt-4 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('rfp.create') }}">Request a proposal</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('vendor.create') }}">Vendor prequalification</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('hse') }}">HSE &amp; compliance</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('industries') }}">Industries</a></li>
                </ul>
                <h2 class="ptw mt-6 text-white/60">Company</h2>
                <ul class="mt-4 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('group') }}">Group companies</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('leadership') }}">Leadership</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('careers') }}">Careers</a></li>
                </ul>
            </div>
            <div>
                <h2 class="ptw text-white/60">Head office</h2>
                <address class="mt-4 not-italic leading-relaxed">
                    {{ setting('address_line') }}<br>{{ setting('address_city') }}, Guyana
                </address>
                @if (setting('office_hours'))<p class="mt-2 text-white/65">{{ setting('office_hours') }}</p>@endif
                @if ($email)<p class="mt-3"><a class="inline-flex min-h-11 items-center break-all hover:text-white" href="mailto:{{ $email }}">{{ $email }}</a></p>@endif
                <p class="mt-3 flex flex-wrap gap-x-5 gap-y-1">
                    @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn'] as $k => $label)
                        @if (setting($k))<a class="inline-flex min-h-11 items-center gap-1 hover:text-white" href="{{ setting($k) }}" rel="noopener" target="_blank">{{ $label }}<x-icon name="external" class="h-3.5 w-3.5" /></a>@endif
                    @endforeach
                </p>
                <p class="coord mt-5 text-white/55">06°48′N · 058°09′W</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="wrap flex flex-col gap-3 py-6 text-sm text-white/60 md:flex-row md:items-center md:justify-between">
                <p>© {{ date('Y') }} {{ rtrim(sheriff('legal_name'), '.') }}. All rights reserved.</p>
                <p class="flex flex-wrap gap-x-5 gap-y-1">
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('privacy') }}">Privacy</a>
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('credits') }}">Photo credits</a>
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('sitemap') }}">Sitemap</a>
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('login') }}">Staff sign-in</a>
                </p>
            </div>
        </div>
    </footer>

    @if ($mobileBar)
        {{-- Thumb-reach actions on phones: call, alarm fault, proposal --}}
        <nav aria-label="Quick actions" class="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-brand-950/95 p-3 backdrop-blur lg:hidden">
            <div class="flex gap-2">
                @if ($phone)<a href="{{ tel_href($phone) }}" class="btn btn-on-dark flex-none px-4" aria-label="Call {{ format_phone($phone) }}"><x-icon name="phone" /></a>@endif
                <a href="{{ route('alarm-service') }}" class="btn btn-on-dark flex-1 whitespace-nowrap px-2 text-[0.9375rem]">Alarm fault</a>
                <a href="{{ route('rfp.create') }}" class="btn btn-blue flex-1 whitespace-nowrap px-2 text-[0.9375rem]">Get a proposal</a>
            </div>
        </nav>
    @endif
</body>
</html>
