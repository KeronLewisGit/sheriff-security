<x-layouts.site title="Careers" description="Security officer and specialist jobs with Sheriff Security Services in Guyana. Apply online with your police clearance and ID.">
    <x-page-header title="Careers at Sheriff" tag="Human resources" no="HR" photo="georgetown-river" plate="Georgetown · Demerara mouth"
        lead="Officers, supervisors and specialists for energy, marine, cash-in-transit and alarm work. Apply online in about ten minutes with clear photos of your documents.">
        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('apply.create') }}" class="btn btn-light">Apply online<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <a href="#openings" class="btn btn-on-dark">{{ $jobs->count() }} open {{ \Illuminate\Support\Str::plural('position', $jobs->count()) }}</a>
        </div>
    </x-page-header>

    <section class="wrap mt-12 grid gap-6 md:mt-16 md:grid-cols-3" aria-label="Why Sheriff">
        <div class="sign"><span class="sign-disc"><x-icon name="plus" /></span><div><h2 class="font-display text-lg font-semibold">Health &amp; life insurance</h2><p class="mt-1 text-ink-2">A plan offered to the workforce at no cost to employees, from January 2024.</p><x-source class="mt-1" keys="stabroek_2023_insurance" /></div></div>
        <div class="sign"><span class="sign-disc"><x-icon name="graduation" /></span><div><h2 class="font-display text-lg font-semibold">Training academy</h2><p class="mt-1 text-ink-2">Oil and gas, HSE, ISPS, first aid and CPR training at the group’s own centre.</p><x-source class="mt-1" keys="sssgy_home" /></div></div>
        <div class="sign"><span class="sign-disc"><x-icon name="award" /></span><div><h2 class="font-display text-lg font-semibold">Award-winning team</h2><p class="mt-1 text-ink-2">{{ sheriff('awards.total') }} of {{ sheriff('awards.of') }} accolades at the GAPSO awards, December {{ sheriff('awards.year') }}.</p><x-source class="mt-1" keys="gapso_2024" /></div></div>
    </section>

    <section id="openings" class="wrap mt-16" aria-labelledby="open-title">
        <h2 id="open-title" class="display-3">Open positions</h2>
        @if ($jobs->isEmpty())
            <div class="panel mt-6 p-6"><p>No positions are advertised right now. You can still send a general application and HR will contact you when something suitable opens.</p><a href="{{ route('apply.create') }}" class="btn btn-primary mt-4">General application</a></div>
        @else
            <ul class="mt-6 divide-y divide-line border-y border-line">
                @foreach ($jobs as $job)
                    <li>
                        <a href="{{ route('careers.show', $job) }}" class="group grid gap-2 py-5 md:grid-cols-[1fr_auto] md:items-center">
                            <span>
                                <span class="coord text-ink-3">{{ $job->department }}</span>
                                <span class="mt-1 block text-xl font-semibold group-hover:text-link">{{ $job->title }}</span>
                                <span class="mt-1 block text-ink-2">{{ $job->summary }}</span>
                            </span>
                            <span class="flex flex-wrap items-center gap-2 text-sm md:justify-end">
                                <x-chip icon="map-pin">{{ $job->location }}</x-chip>
                                <x-chip icon="clock">{{ $job->employment_type->label() }}</x-chip>
                                @if ($job->openings > 1)<x-chip icon="users">{{ $job->openings }} openings</x-chip>@endif
                                <x-icon name="arrow-right" class="ml-2 hidden h-5 w-5 text-link md:block" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="wrap mt-16" aria-labelledby="docs-title">
        <div class="rounded-md border border-line bg-surface p-6 md:p-8">
            <h2 id="docs-title" class="display-3">Have these ready</h2>
            <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['shield', 'Police clearance', 'Required. A clear phone photo is fine.'], ['file', 'National ID or passport', 'Required.'], ['graduation', 'Certificates', 'Optional. Security, first aid, CXC, firearm licence.'], ['briefcase', 'CV', 'Optional.']] as [$icon, $t, $d])
                    <li class="flex gap-3"><x-icon :name="$icon" class="mt-0.5 h-5 w-5 flex-none text-link" /><span><strong>{{ $t }}</strong><span class="block text-sm text-ink-2">{{ $d }}</span></span></li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.site>
