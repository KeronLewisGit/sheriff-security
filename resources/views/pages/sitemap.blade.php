<x-layouts.site title="Sitemap">
    <x-page-header title="Sitemap" tag="Index" no="MAP" lead="Every page on the site." />
    <section class="wrap mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 md:mt-16">
        @foreach ([
            'Company' => [['home', 'Home'], ['group', 'Group companies'], ['leadership', 'Leadership'], ['industries', 'Industries'], ['contact', 'Contact']],
            'Procurement' => [['rfp.create', 'Request a proposal'], ['vendor.create', 'Vendor prequalification'], ['hse', 'HSE & compliance']],
            'Customers & careers' => [['alarm-service', 'Alarm service & fault report'], ['careers', 'Careers'], ['apply.create', 'Apply online']],
            'Policies' => [['privacy', 'Privacy'], ['credits', 'Photo credits']],
        ] as $heading => $links)
            <div>
                <h2 class="ptw">{{ $heading }}</h2>
                <ul class="mt-3 space-y-1">@foreach ($links as [$r, $l])<li><a class="inline-flex min-h-11 items-center text-link" href="{{ route($r) }}">{{ $l }}</a></li>@endforeach</ul>
            </div>
        @endforeach
        <div>
            <h2 class="ptw">Services</h2>
            <ul class="mt-3 space-y-1"><li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('services') }}">All services</a></li>@foreach (sheriff('services') as $slug => $s)<li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('services.show', $slug) }}">{{ $s['title'] }}</a></li>@endforeach</ul>
        </div>
        @if ($jobs->isNotEmpty())
            <div>
                <h2 class="ptw">Open positions</h2>
                <ul class="mt-3 space-y-1">@foreach ($jobs as $job)<li><a class="inline-flex min-h-11 items-center text-link" href="{{ route('careers.show', $job) }}">{{ $job->title }}</a></li>@endforeach</ul>
            </div>
        @endif
    </section>
</x-layouts.site>
