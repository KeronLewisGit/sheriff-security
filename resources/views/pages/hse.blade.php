@php
    $awards = sheriff('awards');
    $welfare = sheriff('welfare');
@endphp
<x-layouts.site title="HSE & compliance" description="ISO 9001:2015 and ISO 45001:2018 certified. Local Content Secretariat certified. GAPSO 2024 awards. Request Sheriff Security's vendor prequalification pack.">
    <x-page-header title="HSE & compliance" tag="Quality, health & safety" no="QHSE" photo="offshore-patrol" plate="Atlantic approaches · 06°55′N 058°15′W"
        lead="What a procurement or HSE team needs to see before a security contractor goes on site, in one place, with the documents a request away.">
        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('vendor.create') }}" class="btn btn-light"><x-icon name="clipboard" class="h-4 w-4" />Request the vendor pack</a>
            <a href="#certifications" class="btn btn-on-dark">Certifications</a>
        </div>
    </x-page-header>

    <section id="certifications" class="wrap mt-12 md:mt-16" aria-labelledby="cert-title">
        <x-ptw no="01">Management systems &amp; registrations</x-ptw>
        <h2 id="cert-title" class="display-2 mt-3">Certified, and documented</h2>
        <div class="mt-8 grid gap-5 md:grid-cols-2">
            @foreach (sheriff('certifications') as $c)
                <div class="sign" @if ($c['status'] === 'pursuing') style="border-style: dashed" @endif>
                    <span class="sign-disc"><x-icon :name="match (true) { str_contains($c['code'], '45001') => 'hard-hat', str_contains($c['code'], '27001') => 'lock', str_contains($c['code'], 'Local') => 'flag', default => 'clipboard' }" /></span>
                    <div>
                        <h3 class="font-display text-xl font-semibold">{{ $c['code'] }}</h3>
                        <p class="mt-1 text-ink-2">{{ $c['title'] }}</p>
                        <p class="mt-2"><x-chip :tone="$c['status'] === 'certified' ? 'good' : 'neutral'" :icon="$c['status'] === 'certified' ? 'check' : 'clock'">{{ $c['status'] === 'certified' ? 'Certified' : 'Being pursued (2023)' }}</x-chip></p>
                    </div>
                </div>
            @endforeach
        </div>
        <x-source class="mt-4" keys="oilnow_2023" />
        <p class="mt-2 text-sm text-ink-3 measure">Certificate numbers, issuing bodies and expiry dates are kept current in the vendor pack, not on this page.</p>
    </section>

    {{-- The vendor pack, explained --}}
    <section class="relative isolate mt-20 overflow-hidden bg-brand-900 py-16 text-white on-dark md:py-20" aria-labelledby="pack-title">
        <div class="graticule absolute inset-0 -z-10" aria-hidden="true"></div>
        <div class="wrap grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <x-ptw no="02">Vendor prequalification</x-ptw>
                <h2 id="pack-title" class="display-2 mt-3">The supplier pack, without the email chain</h2>
                <p class="lead mt-4 text-white/85">ISO certificates, insurance and HSE policy are confidential to Sheriff, so they are not posted publicly. Procurement teams request them here and get them the same way every time.</p>
                <a href="{{ route('vendor.create') }}" class="btn btn-light mt-7">Request the vendor pack<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            </div>
            <ol class="steps space-y-6">
                <li><strong class="text-white">Request.</strong> <span class="text-white/80">Choose the documents and tell us the organisation and tender or registration they are for.</span></li>
                <li><strong class="text-white">Approval.</strong> <span class="text-white/80">A Sheriff manager checks the request. Nothing is sent automatically.</span></li>
                <li><strong class="text-white">Private link.</strong> <span class="text-white/80">You receive a signed download link that expires after {{ setting('vendor_link_days', '7') }} days and can be withdrawn at any time.</span></li>
                <li><strong class="text-white">Logged.</strong> <span class="text-white/80">Every download is recorded, so Sheriff knows exactly where its documents went.</span></li>
            </ol>
        </div>
    </section>

    <section class="wrap mt-20 grid gap-10 lg:grid-cols-2 lg:gap-16" aria-labelledby="train-title">
        <div>
            <x-ptw no="03">Training</x-ptw>
            <h2 id="train-title" class="display-3 mt-3">Sheriff Training Academy</h2>
            <p class="mt-3 text-ink-2 measure">The group runs its own training centre for oil and gas, HSE and ISPS training. Topics listed on the company’s website:</p>
            <ul class="mt-5 grid gap-2.5 sm:grid-cols-2">
                @foreach (sheriff('training') as $t)
                    <li class="flex gap-2.5"><x-icon name="graduation" class="mt-0.5 h-5 w-5 flex-none text-link" />{{ $t }}</li>
                @endforeach
            </ul>
            <x-source class="mt-4" :keys="['sssgy_home', 'sssgy_services']" />
        </div>
        <div>
            <x-ptw no="04">Workforce welfare</x-ptw>
            <h2 class="display-3 mt-3">{{ $welfare['headline'] }}</h2>
            <p class="mt-3 text-ink-2 measure">{{ $welfare['body'] }}</p>
            <blockquote class="mt-5 border-l-4 border-blue pl-5 font-display text-xl font-medium leading-snug">“{{ $welfare['quote'] }}”<footer class="mt-2 font-sans text-base font-normal text-ink-2">{{ $welfare['quote_by'] }}</footer></blockquote>
            <x-source class="mt-4" :keys="$welfare['source']" />
        </div>
    </section>

    <section class="wrap mt-20" aria-labelledby="awards-title">
        <x-ptw no="05">Recognition</x-ptw>
        <h2 id="awards-title" class="display-3 mt-3">GAPSO awards {{ $awards['year'] }}: {{ $awards['total'] }} of {{ $awards['of'] }} accolades</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-2">
            <div class="panel p-6">
                <h3 class="font-bold">Company awards</h3>
                <ul class="mt-3 space-y-2">@foreach ($awards['company'] as $a)<li class="flex gap-2.5"><x-icon name="award" class="mt-0.5 h-5 w-5 flex-none text-link" />{{ $a }}</li>@endforeach</ul>
            </div>
            <div class="panel p-6">
                <h3 class="font-bold">Leadership awards</h3>
                <ul class="mt-3 space-y-3">@foreach ($awards['people'] as $p)<li><strong>{{ $p['name'] }}</strong><span class="block text-ink-2">{{ implode(' · ', $p['awards']) }}</span></li>@endforeach</ul>
            </div>
        </div>
        <x-source class="mt-4" :keys="$awards['source']" />
    </section>

    <section class="wrap mt-20" aria-labelledby="press-title">
        <h2 id="press-title" class="display-3">In the press</h2>
        <ul class="mt-5 divide-y divide-line border-y border-line">
            @foreach (['oilnow_2023', 'gapso_2024', 'stabroek_2023_insurance', 'stabroek_2023_heli', 'kaieteur_2021'] as $k)
                @php($s = source_link($k))
                <li><a href="{{ $s['url'] }}" class="flex min-h-14 items-center justify-between gap-4 py-3 hover:text-link" rel="noopener" target="_blank"><span>{{ $s['label'] }}</span><x-icon name="external" class="h-4 w-4 flex-none" /></a></li>
            @endforeach
        </ul>
    </section>
</x-layouts.site>
