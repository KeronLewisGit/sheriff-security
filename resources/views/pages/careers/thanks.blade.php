<x-layouts.site title="Application sent">
    <x-page-header title="Application sent" tag="Human resources" no="APP" lead="Thank you. Sheriff’s HR team reviews every application and will call you if you are shortlisted for screening." />
    <section class="wrap mt-12 md:mt-16">
        <div class="tag-card max-w-xl">
            <div class="tag-card-head"><span class="ptw">Your reference</span><x-icon name="check-circle" class="h-6 w-6 text-good" /></div>
            <div class="p-6">
                <p class="ref text-3xl text-link">{{ $reference }}</p>
                <p class="mt-3 text-ink-2">Keep this number. Quote it if you call HR about your application.</p>
            </div>
        </div>
        <a href="{{ route('careers') }}" class="btn btn-outline mt-8">Back to careers</a>
    </section>
</x-layouts.site>
