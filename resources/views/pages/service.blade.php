<x-layouts.site :title="$service['title']" :description="$service['summary']" :og-image="$service['photo']">
    <x-page-header :title="$service['title']" :tag="$service['short']" :no="$service['tag']" :photo="$service['photo']" :lead="$service['summary']">
        <nav class="mt-6 text-sm text-white/70" aria-label="Breadcrumb"><a href="{{ route('services') }}" class="inline-flex min-h-11 items-center underline underline-offset-2 hover:text-white">Services</a> <span aria-hidden="true">/</span> <span aria-current="page">{{ $service['short'] }}</span></nav>
    </x-page-header>

    <section class="wrap mt-12 grid gap-10 md:mt-16 lg:grid-cols-[1.5fr_1fr] lg:gap-16">
        <div>
            <h2 class="display-3">What the scope covers</h2>
            <ul class="mt-6 divide-y divide-line border-y border-line">
                @foreach ($service['points'] as $point)
                    <li class="flex gap-4 py-4"><span class="coord mt-1 w-10 flex-none text-ink-3">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span class="text-lg">{{ $point }}</span></li>
                @endforeach
            </ul>
            <x-source class="mt-4" :keys="$service['source']" />

            <h2 class="display-3 mt-14">How a contract starts</h2>
            <ol class="steps mt-6 space-y-6 measure">
                <li><strong>Request a proposal.</strong> Site, posts, hours and whether officers are armed. Attach your scope or tender documents.</li>
                <li><strong>Site survey.</strong> A supervisor walks the site, reviews risks and HSE requirements, and confirms the post orders.</li>
                <li><strong>Proposal and mobilisation.</strong> A written proposal with staffing, supervision and reporting, then officers are briefed and deployed.</li>
            </ol>
        </div>
        <aside class="space-y-5">
            <div class="tag-card">
                <div class="tag-card-head"><span class="ptw">Quote this scope</span><x-icon :name="$service['icon']" class="h-6 w-6 text-link" /></div>
                <div class="p-5">
                    <p class="text-ink-2">Target response: within {{ setting('rfp_response_hours', '24') }} hours of a complete request.</p>
                    <a href="{{ route('rfp.create', ['service' => $slug]) }}" class="btn btn-blue mt-4 w-full">Request a proposal</a>
                    <a href="{{ route('vendor.create') }}" class="btn btn-outline mt-3 w-full">Get the vendor pack</a>
                </div>
            </div>
            @if ($slug === 'alarms-and-monitoring')
                <div class="hivis-note"><p class="flex gap-2 font-bold"><x-icon name="bell" class="h-5 w-5 flex-none" />Existing alarm customer?</p><p class="mt-1">Report a fault and track it by ticket number. <a class="u-link" href="{{ route('alarm-service') }}">Alarm service</a></p></div>
            @endif
        </aside>
    </section>

    <section class="wrap mt-20" aria-labelledby="other">
        <h2 id="other" class="display-3">Other services</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (array_slice($others, 0, 3, true) as $s => $o)
                <x-service-card :slug="$s" :service="$o" />
            @endforeach
        </div>
    </section>
</x-layouts.site>
