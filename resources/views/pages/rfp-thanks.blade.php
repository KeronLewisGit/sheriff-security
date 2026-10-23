<x-layouts.site title="Request received">
    <x-page-header title="Request received" tag="Business development" no="RFP" lead="Thank you. Your request for proposal is with Sheriff’s business development team." />
    <section class="wrap mt-12 md:mt-16">
        <div class="tag-card max-w-xl">
            <div class="tag-card-head"><span class="ptw">Your reference</span><x-icon name="check-circle" class="h-6 w-6 text-good" /></div>
            <div class="p-6">
                <p class="ref text-3xl text-link">{{ $reference }}</p>
                <p class="mt-3 text-ink-2">Quote it in any email or call. A copy has been sent to your inbox. Our target is to respond within {{ setting('rfp_response_hours', '24') }} hours.</p>
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('vendor.create') }}" class="btn btn-blue">Request the vendor pack</a>
            <a href="{{ route('hse') }}" class="btn btn-outline">HSE &amp; compliance</a>
        </div>
    </section>
</x-layouts.site>
