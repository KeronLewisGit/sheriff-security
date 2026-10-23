<x-layouts.site title="Vendor pack requested">
    <x-page-header title="Request received" tag="Vendor prequalification" no="VP" lead="A Sheriff manager will review your request. Once it is approved you will receive a private download link by email." />
    <section class="wrap mt-12 md:mt-16">
        <div class="tag-card max-w-xl">
            <div class="tag-card-head"><span class="ptw">Your reference</span><x-icon name="clock" class="h-6 w-6 text-link" /></div>
            <div class="p-6">
                <p class="ref text-3xl text-link">{{ $reference }}</p>
                <p class="mt-3 text-ink-2">The link will work for {{ setting('vendor_link_days', '7') }} days. If it expires before you need it, simply request the pack again.</p>
            </div>
        </div>
        <a href="{{ route('rfp.create') }}" class="btn btn-blue mt-8">Request a proposal too</a>
    </section>
</x-layouts.site>
