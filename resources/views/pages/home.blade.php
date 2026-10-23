<x-layouts.site>
    @php
        $services = sheriff('services');
        $awards = sheriff('awards');
        $certs = sheriff('certifications');
    @endphp

    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-brand-950 text-white on-dark">
        <div class="hero-photo absolute inset-0 -z-20">
            <x-photo key="fpso-sunset" alt="" :eager="true" sizes="100vw" class="h-full w-full" img-class="object-[70%_center]" />
        </div>
        <div class="hero-scrim absolute inset-0 -z-10"></div>
        <div class="graticule absolute inset-0 -z-10 opacity-70" aria-hidden="true"></div>
        <div class="wrap grid items-end gap-10 pb-28 pt-24 md:pb-36 md:pt-32 lg:grid-cols-[1.35fr_1fr] lg:gap-16 lg:pt-36">
            <div>
                <x-ptw no="EST. {{ sheriff('founded') }}">Georgetown, Guyana</x-ptw>
                <h1 class="display-1 mt-5 max-w-3xl">Security built for Guyana’s oil and gas era</h1>
                <p class="lead mt-6 text-white/85">Guarding, marine and air patrol, cash in transit and alarm monitoring, run under ISO&nbsp;9001:2015 and ISO&nbsp;45001:2018 management systems by the company OilNOW called “the prototype for oil and gas security in Guyana”.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('rfp.create') }}" class="btn btn-blue">Request a proposal<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('vendor.create') }}" class="btn btn-on-dark"><x-icon name="clipboard" class="h-4 w-4" />Get the vendor pack</a>
                </div>
            </div>

            {{-- Compliance at a glance: what a procurement officer checks first --}}
            <aside class="tag-card text-ink" aria-labelledby="glance">
                <div class="tag-card-head">
                    <h2 id="glance" class="font-display text-sm font-semibold uppercase tracking-[0.08em] text-ink-2">Compliance at a glance</h2>
                    <span class="coord text-ink-3">QHSE</span>
                </div>
                <ul class="divide-y divide-line px-5">
                    @foreach ($certs as $c)
                        <li class="flex items-start gap-3 py-3">
                            @if ($c['status'] === 'certified')
                                <x-icon name="check-circle" class="mt-0.5 h-5 w-5 flex-none text-good" />
                            @else
                                <x-icon name="clock" class="mt-0.5 h-5 w-5 flex-none text-ink-3" />
                            @endif
                            <span><strong class="font-display font-semibold">{{ $c['code'] }}</strong><span class="block text-sm text-ink-2">{{ $c['title'] }}{{ $c['status'] === 'pursuing' ? ' · in progress (2023)' : '' }}</span></span>
                        </li>
                    @endforeach
                    <li class="flex items-start gap-3 py-3">
                        <x-icon name="award" class="mt-0.5 h-5 w-5 flex-none text-link" />
                        <span><strong class="font-display font-semibold">GAPSO {{ $awards['year'] }}</strong><span class="block text-sm text-ink-2">{{ $awards['total'] }} of the {{ $awards['of'] }} accolades at the first awards</span></span>
                    </li>
                </ul>
                <a href="{{ route('hse') }}" class="m-5 mt-1 inline-flex min-h-11 items-center gap-1.5 font-bold text-link">HSE &amp; compliance detail<x-icon name="arrow-right" class="h-4 w-4" /></a>
            </aside>
        </div>
        <p class="plate !bottom-auto !left-auto right-0 top-0 hidden md:inline-flex" aria-hidden="true"><b>FPSO</b> Illustrative · offshore production vessel</p>
    </section>

    {{-- Three doors: the jobs people come to the site to do --}}
    <section class="wrap relative z-10 -mt-14 md:-mt-16" aria-label="Get started">
        <div class="grid gap-px overflow-hidden rounded-md border border-line bg-line shadow-md md:grid-cols-3">
            @foreach ([
                ['rfp.create', 'file', 'Need officers on site?', 'Request a proposal', 'Tell us the site, the posts and the start date. Onshore, shorebase, port or offshore support. Our target is a reply within '.setting('rfp_response_hours', '24').' hours.'],
                ['vendor.create', 'clipboard', 'Prequalifying suppliers?', 'Get the vendor pack', 'ISO certificates, insurance and HSE policy in one request. Approved by our team, delivered as a private, time-limited download link.'],
                ['alarm-service', 'bell', 'Sheriff Alarms customer?', 'Report a fault', 'Log a fault or service visit, get a ticket number, and check its status any time with your email address.'],
            ] as [$route, $icon, $kicker, $cta, $body])
                <a href="{{ route($route) }}" class="group flex flex-col bg-surface p-6 hover:bg-blue-soft/60 md:p-7">
                    <span class="flex items-center gap-3 text-ink-2"><x-icon :name="$icon" class="h-5 w-5 text-link" /><span class="text-sm font-bold uppercase tracking-[0.06em]">{{ $kicker }}</span></span>
                    <span class="font-display mt-3 text-2xl font-semibold">{{ $cta }}</span>
                    <span class="mt-2 text-ink-2">{{ $body }}</span>
                    <span class="mt-4 inline-flex items-center gap-1.5 font-bold text-link">Start<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Services --}}
    <section class="wrap mt-24 md:mt-28" aria-labelledby="services-title">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <x-ptw no="PTW">Scope of services</x-ptw>
                <h2 id="services-title" class="display-2 mt-3 max-w-2xl">Six lines of work, one permit to operate</h2>
            </div>
            <a href="{{ route('services') }}" class="btn btn-outline">All services</a>
        </div>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $slug => $service)
                <div data-reveal style="--reveal-delay: {{ $loop->index * 60 }}ms"><x-service-card :slug="$slug" :service="$service" /></div>
            @endforeach
        </div>
    </section>

    {{-- Energy split --}}
    <section class="mt-24 md:mt-28" aria-labelledby="energy-title">
        <div class="wrap grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <x-photo key="fpso-helideck" alt="A floating production vessel with a helideck at sea" sizes="(min-width: 1024px) 50vw, 100vw" class="aspect-[4/3] rounded-md" plate="<b>06°N</b> Offshore support · illustrative" />
            <div>
                <x-ptw no="PTW-01">Energy sector</x-ptw>
                <h2 id="energy-title" class="display-2 mt-3">When procurement Googles you first</h2>
                <p class="mt-5 text-ink-2 measure">Operators and their contractors prequalify every supplier. Sheriff brings the paperwork they look for: certified quality and occupational health and safety management systems, Local Content Secretariat certification, and officers trained in oil and gas, HSE and ISPS at the group’s own academy.</p>
                <ul class="mt-6 space-y-3">
                    @foreach ($services['oil-and-gas']['points'] as $point)
                        <li class="flex gap-3"><x-icon name="check" class="mt-1 h-5 w-5 flex-none text-link" /><span>{{ $point }}</span></li>
                    @endforeach
                </ul>
                <x-source class="mt-5" :keys="['oilnow_2023', 'stabroek_2023_heli']" />
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('services.show', 'oil-and-gas') }}" class="btn btn-primary">Oil &amp; gas security</a>
                    <a href="{{ route('industries') }}" class="btn btn-outline">Industries we serve</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Water and air --}}
    <section class="relative isolate mt-24 overflow-hidden bg-brand-900 py-20 text-white on-dark md:mt-28 md:py-24" aria-labelledby="water-title">
        <div class="graticule absolute inset-0 -z-10" aria-hidden="true"></div>
        <div class="wrap">
            <x-ptw no="PTW-02/06">Marine &amp; air</x-ptw>
            <h2 id="water-title" class="display-2 mt-3 max-w-3xl">On the Demerara, and in the air</h2>
            <div class="mt-10 grid gap-6 md:grid-cols-2">
                <a href="{{ route('services.show', 'marine-and-port') }}" class="group overflow-hidden rounded-md bg-brand-800">
                    <x-photo key="demerara-patrol" alt="A patrol boat on the Demerara River" sizes="(min-width: 768px) 50vw, 100vw" class="aspect-[16/9]" plate="<b>DEMERARA R.</b> 06°49′N 058°10′W" />
                    <div class="p-6">
                        <h3 class="font-display text-2xl font-semibold">Marine &amp; port security</h3>
                        <p class="mt-2 text-white/80">Armed marine patrol on the Demerara River, with the Berbice River announced next, and Sheriff Port &amp; Marine within the group.</p>
                        <span class="mt-4 inline-flex items-center gap-1.5 font-bold text-sky">Marine scope<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                    </div>
                </a>
                <a href="{{ route('services.show', 'air-patrol') }}" class="group overflow-hidden rounded-md bg-brand-800">
                    <x-photo key="ogle-airport" alt="Eugene F. Correia International Airport at Ogle, Georgetown" sizes="(min-width: 768px) 50vw, 100vw" class="aspect-[16/9]" plate="<b>OGLE</b> 06°48′N 058°06′W" />
                    <div class="p-6">
                        <h3 class="font-display text-2xl font-semibold">Armed air patrol</h3>
                        <p class="mt-2 text-white/80">A Bell 505 helicopter added for offshore services, reported by Stabroek News as the first of its type for private use in Guyana.</p>
                        <span class="mt-4 inline-flex items-center gap-1.5 font-bold text-sky">Air patrol scope<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                    </div>
                </a>
            </div>
            <p class="mt-6 text-sm text-white/60">Photos: Wikimedia Commons, illustrative of the locations. See <a href="{{ route('credits') }}" class="u-link">photo credits</a>.</p>
        </div>
    </section>

    {{-- Awards --}}
    <section class="wrap mt-24 md:mt-28" aria-labelledby="awards-title">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.4fr] lg:gap-16">
            <div>
                <x-ptw no="GAPSO {{ $awards['year'] }}">Recognition</x-ptw>
                <h2 id="awards-title" class="display-3 mt-3 !text-3xl md:!text-4xl">The most-awarded firm at the first GAPSO awards</h2>
                <p class="mt-6 flex items-baseline gap-3"><span class="font-display text-7xl font-bold leading-none text-link num">{{ $awards['total'] }}</span><span class="text-lg text-ink-2">of {{ $awards['of'] }} accolades from the Guyana Association of Private Security Organisations, December {{ $awards['year'] }}</span></p>
                <x-source class="mt-4" :keys="$awards['source']" />
            </div>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($awards['company'] as $award)
                    <li class="flex items-start gap-3 rounded-md border border-line bg-surface p-4" data-reveal style="--reveal-delay: {{ $loop->index * 40 }}ms"><x-icon name="award" class="mt-0.5 h-5 w-5 flex-none text-link" /><span class="font-medium">{{ $award }}</span></li>
                @endforeach
                <li class="flex items-start gap-3 rounded-md border border-dashed border-line-strong p-4 sm:col-span-2"><x-icon name="users" class="mt-0.5 h-5 w-5 flex-none text-ink-3" /><span class="text-ink-2">Plus four leadership awards for Ameir Ahmad and Radica Ramdeen. <a href="{{ route('leadership') }}" class="u-link">Meet the leadership</a></span></li>
            </ul>
        </div>
    </section>

    {{-- HSE + people --}}
    <section class="wrap mt-24 md:mt-28" aria-labelledby="hse-title">
        <x-ptw no="HSE">Health, safety &amp; people</x-ptw>
        <h2 id="hse-title" class="display-2 mt-3 max-w-3xl">Management systems, not slogans</h2>
        <div class="mt-10 grid gap-5 md:grid-cols-2">
            <div class="sign">
                <span class="sign-disc"><x-icon name="clipboard" /></span>
                <div><h3 class="font-display text-xl font-semibold">ISO 9001:2015</h3><p class="mt-1 text-ink-2">A certified quality management system: documented procedures, audits and corrective action across operations.</p></div>
            </div>
            <div class="sign">
                <span class="sign-disc"><x-icon name="hard-hat" /></span>
                <div><h3 class="font-display text-xl font-semibold">ISO 45001:2018</h3><p class="mt-1 text-ink-2">A certified occupational health and safety management system for officers working on energy, marine and commercial sites.</p></div>
            </div>
        </div>
        <figure class="mt-5 grid gap-6 rounded-md bg-surface p-6 md:grid-cols-[auto_1fr] md:items-center md:p-8">
            <span class="sign-disc !m-0"><x-icon name="plus" /></span>
            <div>
                <blockquote class="font-display text-2xl font-medium leading-snug">“{{ sheriff('welfare.quote') }}”</blockquote>
                <figcaption class="mt-3 text-ink-2">{{ sheriff('welfare.quote_by') }}, on the health and life insurance plan offered to the workforce at no cost from January 2024.</figcaption>
                <x-source class="mt-2" :keys="sheriff('welfare.source')" />
            </div>
        </figure>
    </section>

    {{-- Careers --}}
    <section class="wrap mt-24 md:mt-28" aria-labelledby="careers-title">
        <div class="relative isolate overflow-hidden rounded-md bg-brand-900 text-white on-dark">
            <div class="absolute inset-0 -z-20"><x-photo key="georgetown-river" alt="" sizes="100vw" class="h-full w-full" /></div>
            <div class="hero-scrim absolute inset-0 -z-10"></div>
            <div class="grid gap-8 p-8 md:p-12 lg:grid-cols-[1.5fr_1fr] lg:items-end">
                <div>
                    <x-ptw no="HR">Careers</x-ptw>
                    <h2 id="careers-title" class="display-2 mt-3">Train at the academy. Work where it matters.</h2>
                    <p class="lead mt-4 text-white/85">Officers, supervisors and specialists for energy, marine, cash-in-transit and alarm work, with HSE, ISPS and first aid training and a health and life insurance plan at no cost to you.</p>
                </div>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('careers') }}" class="btn btn-light">{{ $openJobs ? $openJobs.' open '.\Illuminate\Support\Str::plural('position', $openJobs) : 'See careers' }}<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('apply.create') }}" class="btn btn-on-dark">Apply online</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
