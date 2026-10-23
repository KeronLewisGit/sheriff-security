<x-layouts.site title="Security services" description="Oil and gas security, marine and port security, cash in transit, alarms and monitoring, manned guarding and armed air patrol in Guyana.">
    <x-page-header title="Security services" tag="Scope of services" no="PTW" photo="harbour-bridge" plate="Demerara Harbour Bridge · 06°47′N 058°10′W"
        lead="Six lines of business, each named in public reporting on Sheriff. Every scope below can be quoted for a single post or a multi-site contract." />

    <section class="wrap mt-12 md:mt-16">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (sheriff('services') as $slug => $service)
                <x-service-card :slug="$slug" :service="$service" />
            @endforeach
        </div>
    </section>

    <section class="wrap mt-20" aria-labelledby="also">
        <div class="grid gap-8 rounded-md border border-line bg-surface p-6 md:grid-cols-[1fr_1.4fr] md:p-10">
            <div>
                <x-ptw no="+">Alongside every contract</x-ptw>
                <h2 id="also" class="display-3 mt-3">Support services named on Sheriff’s own site</h2>
            </div>
            <div>
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach (['Threat and risk assessment', 'Crisis management', 'Background checks', '24-hour command centre', 'Silvertrac guard-tracking software', 'GPS fleet tracking', 'First aid and CPR team', 'Training academy: oil & gas, HSE, ISPS'] as $item)
                        <li class="flex gap-2.5"><x-icon name="check" class="mt-1 h-4 w-4 flex-none text-link" />{{ $item }}</li>
                    @endforeach
                </ul>
                <x-source class="mt-5" :keys="['sssgy_services', 'sssgy_home', 'oilnow_2023']" />
            </div>
        </div>
    </section>

    @include('partials.cta-band')
</x-layouts.site>
