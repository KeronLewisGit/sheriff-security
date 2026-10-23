<x-layouts.site title="Industries" description="Security for oil and gas, maritime and port, banking and cash handling, commercial, diplomatic and residential clients in Guyana.">
    <x-page-header title="Industries we serve" tag="Sectors" no="IND" photo="stelling" plate="Georgetown waterfront · 06°49′N 058°10′W"
        lead="The sectors Sheriff’s services are built for. We describe sectors here, not clients: references are shared privately during prequalification." />

    <section class="wrap mt-12 space-y-6 md:mt-16">
        @foreach (sheriff('industries') as $industry)
            <article class="grid overflow-hidden rounded-md border border-line bg-surface md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]" data-reveal>
                <x-photo :key="$industry['photo']" alt="" sizes="(min-width: 768px) 40vw, 100vw" class="aspect-[16/9] md:aspect-auto md:h-full" />
                <div class="p-6 md:p-8">
                    <p class="flex items-center gap-3"><x-icon :name="$industry['icon']" class="h-6 w-6 text-link" /><span class="coord text-ink-3">IND-{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></p>
                    <h2 class="display-3 mt-3">{{ $industry['title'] }}</h2>
                    <p class="mt-3 text-ink-2 measure">{{ $industry['body'] }}</p>
                    <p class="mt-5 text-sm font-bold uppercase tracking-[0.06em] text-ink-3">Relevant services</p>
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($industry['services'] as $s)
                            <li><a href="{{ route('services.show', $s) }}" class="inline-flex min-h-11 items-center gap-2 rounded-sm border border-line-strong px-3.5 font-medium hover:border-ink"><x-icon :name="sheriff('services.'.$s.'.icon')" class="h-4 w-4 text-link" />{{ sheriff('services.'.$s.'.short') }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
        <p class="text-sm text-ink-3">Photos are illustrative views of Guyana and the sector; see <a class="u-link" href="{{ route('credits') }}">photo credits</a>.</p>
    </section>

    @include('partials.cta-band')
</x-layouts.site>
