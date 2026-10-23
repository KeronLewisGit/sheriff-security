<section class="wrap mt-20" aria-label="Next steps">
    <div class="relative isolate grid gap-6 overflow-hidden rounded-md bg-brand-900 p-8 text-white on-dark md:grid-cols-[1.4fr_1fr] md:items-center md:p-12">
        <div class="graticule absolute inset-0 -z-10" aria-hidden="true"></div>
        <div>
            <x-ptw no="RFP">Next step</x-ptw>
            <h2 class="display-3 mt-3">Tell us about the site. We’ll come and survey it.</h2>
            <p class="mt-3 text-white/80">Onshore yard, shorebase, wharf, branch network or office: send the basics and any tender documents, and business development will be in touch.</p>
        </div>
        <div class="flex flex-col gap-3">
            <a href="{{ route('rfp.create') }}" class="btn btn-light">Request a proposal<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <a href="{{ route('vendor.create') }}" class="btn btn-on-dark">Vendor prequalification pack</a>
        </div>
    </div>
</section>
