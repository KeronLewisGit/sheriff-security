@php
    use App\Enums\Industry;
    use App\Enums\SiteType;
    use App\Models\ProposalRequest;
    $industries = collect(Industry::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
@endphp
<x-layouts.site title="Request a proposal" description="Request a security proposal from Sheriff Security Services for an onshore site, shorebase, port, offshore support or commercial premises in Guyana.">
    <x-page-header title="Request a proposal" tag="Business development" no="RFP"
        lead="Five minutes now saves a week of back-and-forth. Tell us the site, the posts and the start date; attach your scope or tender pack if you have one.">
        <p class="mt-6 inline-flex items-center gap-2 rounded-sm bg-white/10 px-3 py-2 text-sm"><x-icon name="clock" class="h-4 w-4 text-sky" />Response target: {{ setting('rfp_response_hours', '24') }} hours from a complete request</p>
    </x-page-header>

    <section class="wrap mt-12 grid gap-10 md:mt-16 lg:grid-cols-[1.6fr_1fr] lg:gap-14">
        <form method="POST" action="{{ route('rfp.store') }}" enctype="multipart/form-data" class="space-y-8" novalidate>
            @csrf
            <div class="hidden" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
            @if ($errors->any())
                <div class="hivis-note" role="alert"><p class="font-bold">Please check {{ $errors->count() === 1 ? 'one field' : $errors->count().' fields' }} below.</p></div>
            @endif

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>01</b><span>Your organisation</span></legend>
                <x-field name="company" label="Company or organisation" autocomplete="organization" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="contact_name" label="Your name" autocomplete="name" required />
                    <x-field name="job_title" label="Job title" optional autocomplete="organization-title" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="email" label="Work email" type="email" autocomplete="email" required />
                    <x-field name="phone" label="Phone" type="tel" autocomplete="tel" required hint="Include the country code if outside Guyana." />
                </div>
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>02</b><span>The site</span></legend>
                <x-select name="industry" label="Industry" :options="$industries" required />
                <fieldset>
                    <legend class="label">Type of site</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (SiteType::cases() as $type)
                            <label class="choice"><input type="radio" name="site_type" value="{{ $type->value }}" @checked(old('site_type') === $type->value) required>{{ $type->label() }}</label>
                        @endforeach
                    </div>
                    <x-error for="site_type" />
                </fieldset>
                <x-field name="site_location" label="Site location" required hint="Area, town or facility name, e.g. “Houston, East Bank Demerara” or “Supply base, Region 4”." />
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>03</b><span>Posts &amp; coverage</span></legend>
                <div class="grid gap-5 sm:grid-cols-[10rem_1fr]">
                    <x-field name="posts" label="Posts" type="number" min="1" max="999" inputmode="numeric" required hint="Officer positions on duty at once." />
                    <x-select name="coverage" label="Coverage" :options="ProposalRequest::COVERAGE" required />
                </div>
                <fieldset>
                    <legend class="label">Armed or unarmed?</legend>
                    <div class="grid gap-2 sm:grid-cols-4">
                        @foreach (ProposalRequest::ARMED as $v => $l)
                            <label class="choice"><input type="radio" name="armed" value="{{ $v }}" @checked(old('armed') === $v) required>{{ $l }}</label>
                        @endforeach
                    </div>
                    <x-error for="armed" />
                </fieldset>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="start_date" label="Start date" type="date" optional min="{{ today()->toDateString() }}" />
                    <x-select name="contract_term" label="Contract term" :options="ProposalRequest::TERMS" optional placeholder="Not sure yet" />
                </div>
                <x-field name="details" label="Anything else we should know?" type="textarea" optional rows="5" :value="$preset ? 'Service: '.$preset.\PHP_EOL : null" hint="Risks on site, access or permit-to-work requirements, reporting you need, current provider, tender deadline." />
            </fieldset>

            <fieldset class="group space-y-3">
                <legend class="ptw"><b>04</b><span>Documents</span></legend>
                <x-field name="documents[]" label="Scope, tender or site documents" type="file" optional multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip" hint="Up to 5 files, 10 MB each. PDF, Word, Excel, images or ZIP." />
                @foreach ($errors->get('documents.*') as $messages)
                    <p class="error"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" />{{ $messages[0] }}</p>
                @endforeach
            </fieldset>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button class="btn btn-blue">Send request for proposal<x-icon name="arrow-right" class="nudge h-4 w-4" /></button>
                <p class="text-sm text-ink-3">You’ll get a reference number straight away, and by email.</p>
            </div>
        </form>

        <aside class="space-y-5 lg:sticky lg:top-32 lg:self-start">
            <div class="tag-card">
                <div class="tag-card-head"><span class="ptw">What happens next</span><x-icon name="file" class="h-6 w-6 text-link" /></div>
                <ol class="steps space-y-5 p-5">
                    <li><strong>Reference number</strong> <span class="block text-ink-2">On screen and by email, e.g. RFP-{{ now()->format('y') }}-00042.</span></li>
                    <li><strong>Call-back</strong> <span class="block text-ink-2">Business development reviews it within {{ setting('rfp_response_hours', '24') }} hours.</span></li>
                    <li><strong>Site survey</strong> <span class="block text-ink-2">A supervisor assesses the site and post orders.</span></li>
                    <li><strong>Written proposal</strong> <span class="block text-ink-2">Staffing, supervision, reporting and price.</span></li>
                </ol>
            </div>
            <a href="{{ route('vendor.create') }}" class="flex items-center gap-3 rounded-md border border-line bg-surface p-4 hover:border-blue"><x-icon name="clipboard" class="h-6 w-6 flex-none text-link" /><span><strong>Need our certificates first?</strong><span class="block text-sm text-ink-2">Request the vendor prequalification pack</span></span></a>
        </aside>
    </section>
</x-layouts.site>
