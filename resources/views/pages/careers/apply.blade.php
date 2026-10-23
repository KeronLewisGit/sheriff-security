@php
    use App\Enums\Experience;
    use App\Enums\Region;
    $regions = collect(Region::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
@endphp
<x-layouts.site title="Apply online" description="Apply for a job with Sheriff Security Services. Upload your police clearance and ID.">
    <x-page-header title="{{ $selected ? 'Apply: '.$selected->title : 'Apply online' }}" tag="Human resources" no="APP"
        lead="One page, about ten minutes. Your documents go to Sheriff’s HR team only and are never shown on the website." />

    <section class="wrap mt-12 md:mt-16">
        <form method="POST" action="{{ route('apply.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-8" novalidate>
            @csrf
            <div class="hidden" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
            @if ($errors->any())
                <div class="hivis-note" role="alert"><p class="font-bold">Please check {{ $errors->count() === 1 ? 'one field' : $errors->count().' fields' }} below. Files need to be chosen again.</p></div>
            @endif

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>01</b><span>The job</span></legend>
                <x-select name="job_posting_id" label="Which position?" :options="$jobs->pluck('title', 'id')->all()" :value="$selected?->id" placeholder="General application (any suitable role)" />
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>02</b><span>About you</span></legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="first_name" label="First name" autocomplete="given-name" required />
                    <x-field name="last_name" label="Last name" autocomplete="family-name" required />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="phone" label="Phone" type="tel" autocomplete="tel" required hint="WhatsApp number is fine." />
                    <x-field name="email" label="Email" type="email" optional autocomplete="email" />
                </div>
                <x-select name="region" label="Where do you live?" :options="$regions" required />
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>03</b><span>Experience</span></legend>
                <fieldset>
                    <legend class="label">Security experience</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (Experience::cases() as $e)
                            <label class="choice"><input type="radio" name="experience" value="{{ $e->value }}" @checked(old('experience') === $e->value) required>{{ $e->label() }}</label>
                        @endforeach
                    </div>
                    <x-error for="experience" />
                </fieldset>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="choice"><input type="checkbox" name="armed_licence" value="1" @checked(old('armed_licence'))>I hold a firearm licence</label>
                    <label class="choice"><input type="checkbox" name="offshore_ready" value="1" @checked(old('offshore_ready'))>I can work shift rotations away from home</label>
                </div>
                <x-field name="message" label="Anything else?" type="textarea" optional rows="3" hint="Previous employers, training, availability." />
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>04</b><span>Documents</span></legend>
                <p class="text-ink-2">PDF, Word or a clear photo (JPG or PNG), up to 5 MB each.</p>
                <x-field name="police_clearance" label="Police clearance" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required />
                <x-field name="identification" label="National ID card or passport" type="file" accept=".pdf,.jpg,.jpeg,.png" required />
                <x-field name="certificate[]" label="Certificates" type="file" optional multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" hint="Up to 4: security training, first aid, CXC, firearm licence." />
                @foreach ($errors->get('certificate.*') as $messages)<p class="error"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" />{{ $messages[0] }}</p>@endforeach
                <x-field name="cv" label="CV" type="file" optional accept=".pdf,.doc,.docx" />
            </fieldset>

            <label class="choice items-start"><input type="checkbox" name="consent" value="1" class="mt-0.5" @checked(old('consent')) required><span>Sheriff may keep my details and documents to consider me for work, as described in the <a class="u-link" href="{{ route('privacy') }}">privacy notice</a>.</span></label>
            <x-error for="consent" />

            <button class="btn btn-blue">Send application<x-icon name="arrow-right" class="nudge h-4 w-4" /></button>
        </form>
    </section>
</x-layouts.site>
