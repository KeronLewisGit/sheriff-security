<x-layouts.site title="Group companies" description="Sheriff Security Services and the Sheriff Guyana group: Sheriff Alarms, Sheriff Port & Marine, Sheriff Training Academy, Sheriff Construction and Sheriff Medical Centre.">
    <x-page-header title="The Sheriff group" tag="Group companies" no="GRP" photo="georgetown-coast" plate="Georgetown · 06°48′N 058°09′W"
        lead="Sheriff Security is the core company, established in {{ sheriff('founded') }}. The group has since formed specialist companies around it." />

    <section class="wrap mt-12 md:mt-16">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (sheriff('group') as $co)
                <article class="tag-card h-full">
                    <div class="tag-card-head"><span class="coord text-ink-3">{{ $loop->first ? 'CORE' : 'GRP-'.str_pad((string) $loop->index, 2, '0', STR_PAD_LEFT) }}</span><x-icon :name="$co['icon']" class="h-6 w-6 text-link" /></div>
                    <div class="flex flex-1 flex-col p-5 pt-4">
                        <h2 class="text-xl font-semibold">{{ $co['name'] }}</h2>
                        @if ($co['legal'])<p class="text-sm text-ink-3">{{ $co['legal'] }}</p>@endif
                        <p class="mt-2 text-ink-2">{{ $co['role'] }}</p>
                        <x-source class="mt-auto pt-4" :keys="$co['source']" />
                        @if ($co['route'])<a href="{{ route($co['route']) }}" class="mt-3 inline-flex min-h-11 items-center gap-1.5 font-bold text-link">Go to {{ strtolower(\Illuminate\Support\Str::after($co['name'], 'Sheriff ')) }}<x-icon name="arrow-right" class="h-4 w-4" /></a>@endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="wrap mt-20 grid gap-8 md:grid-cols-2">
        <div class="panel p-6 md:p-8">
            <h2 class="display-3">Head office</h2>
            <p class="mt-3 text-lg">{{ setting('address_line') }}, {{ setting('address_city') }}</p>
            <p class="mt-2 text-ink-2">The group has also announced a three-storey headquarters for Sheriff Security at Palmyra, Region 6 (Berbice).</p>
            <x-source class="mt-3" keys="stabroek_2023_heli" />
        </div>
        <div class="panel flex flex-col justify-between p-6 md:p-8">
            <div>
                <h2 class="display-3">Leadership</h2>
                <p class="mt-3 text-ink-2">The executives named in public reporting, and the GAPSO awards they received.</p>
            </div>
            <a href="{{ route('leadership') }}" class="btn btn-primary mt-6 self-start">Meet the leadership</a>
        </div>
    </section>
</x-layouts.site>
