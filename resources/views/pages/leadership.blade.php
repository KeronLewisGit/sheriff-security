<x-layouts.site title="Leadership" description="The leadership of Sheriff Security Services and the Sheriff Group, Georgetown, Guyana.">
    <x-page-header title="Leadership" tag="Company" no="EXEC" lead="The executives named in public reporting on Sheriff Security and the Sheriff Group. Titles are as last reported." />

    <section class="wrap mt-12 md:mt-16">
        <ul class="grid gap-5 md:grid-cols-2">
            @foreach (sheriff('leadership') as $person)
                <li class="flex gap-5 rounded-md border border-line bg-surface p-6" data-reveal>
                    <span class="grid h-16 w-14 flex-none place-items-center bg-brand-900 font-display text-xl font-bold text-sky" style="clip-path: polygon(50% 0, 100% 25%, 100% 75%, 50% 100%, 0 75%, 0 25%)" aria-hidden="true">{{ collect(explode(' ', $person['name']))->map(fn ($p) => mb_substr($p, 0, 1))->implode('') }}</span>
                    <div>
                        <h2 class="text-xl font-semibold">{{ $person['name'] }}</h2>
                        <p class="font-bold text-link">{{ $person['title'] }}</p>
                        <p class="mt-2 text-ink-2">{{ $person['note'] }}</p>
                        <x-source class="mt-2" :keys="$person['source']" />
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="mt-8 text-sm text-ink-3 measure">Portraits will be added with Sheriff’s own photography.</p>
    </section>
</x-layouts.site>
