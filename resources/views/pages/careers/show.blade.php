@php
    $jsonLd = [array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $job->title,
        'description' => nl2br(e($job->summary."\n\n".$job->duties."\n\n".$job->requirements)),
        'datePosted' => $job->created_at->toDateString(),
        'validThrough' => $job->closes_on?->toDateString(),
        'employmentType' => $job->employment_type->schema(),
        'hiringOrganization' => ['@type' => 'Organization', 'name' => sheriff('name'), 'sameAs' => route('home')],
        'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $job->location, 'addressCountry' => 'GY']],
        'totalJobOpenings' => $job->openings,
    ])];
@endphp
<x-layouts.site :title="$job->title" :description="$job->summary" :json-ld="$jsonLd">
    <x-page-header :title="$job->title" :tag="$job->department" no="JOB" :lead="$job->summary">
        <div class="mt-6 flex flex-wrap gap-2 text-sm">
            <span class="inline-flex items-center gap-1.5 rounded-sm bg-white/10 px-3 py-1.5"><x-icon name="map-pin" class="h-4 w-4 text-sky" />{{ $job->location }}</span>
            <span class="inline-flex items-center gap-1.5 rounded-sm bg-white/10 px-3 py-1.5"><x-icon name="clock" class="h-4 w-4 text-sky" />{{ $job->employment_type->label() }}</span>
            @if ($job->closes_on)<span class="inline-flex items-center gap-1.5 rounded-sm bg-white/10 px-3 py-1.5"><x-icon name="calendar" class="h-4 w-4 text-sky" />Closes {{ $job->closes_on->format('j M Y') }}</span>@endif
        </div>
    </x-page-header>
    <section class="wrap mt-12 grid gap-10 md:mt-16 lg:grid-cols-[1.6fr_1fr]">
        <div class="space-y-10">
            @foreach (['duties' => 'What you will do', 'requirements' => 'What you need'] as $field => $heading)
                @if ($job->lines($field))
                    <div>
                        <h2 class="display-3">{{ $heading }}</h2>
                        <ul class="mt-4 space-y-2.5 measure">@foreach ($job->lines($field) as $line)<li class="flex gap-3"><x-icon name="check" class="mt-1 h-5 w-5 flex-none text-link" />{{ $line }}</li>@endforeach</ul>
                    </div>
                @endif
            @endforeach
        </div>
        <aside class="lg:sticky lg:top-32 lg:self-start">
            <div class="tag-card">
                <div class="tag-card-head"><span class="ptw">Apply</span><x-icon name="briefcase" class="h-6 w-6 text-link" /></div>
                <div class="p-5">
                    <p class="text-ink-2">Have your police clearance and ID ready to upload.</p>
                    <a href="{{ route('apply.create', ['job' => $job->slug]) }}" class="btn btn-blue mt-4 w-full">Apply for this job</a>
                    <a href="{{ route('careers') }}" class="btn btn-outline mt-3 w-full">All positions</a>
                </div>
            </div>
        </aside>
    </section>
</x-layouts.site>
