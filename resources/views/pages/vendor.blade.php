@php use App\Models\VendorRequest; @endphp
<x-layouts.site title="Vendor prequalification pack" description="Procurement teams: request Sheriff Security Services' ISO certificates, insurance and HSE policy. Approved requests get a private, time-limited download link.">
    <x-page-header title="Vendor prequalification pack" tag="For procurement teams" no="VP" photo="osv-shorebase" plate="Supply vessel · illustrative"
        lead="Request Sheriff’s compliance documents for supplier registration, a tender or a contractor audit. A manager approves each request and you receive a private download link." />

    <section class="wrap mt-12 grid gap-10 md:mt-16 lg:grid-cols-[1.6fr_1fr] lg:gap-14">
        <form method="POST" action="{{ route('vendor.store') }}" class="space-y-8" novalidate>
            @csrf
            <div class="hidden" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>

            <fieldset class="group">
                <legend class="ptw"><b>01</b><span>Documents you need</span></legend>
                @if ($documents->isEmpty())
                    <p class="mt-2 text-ink-2">The document list is being prepared. Please <a class="u-link" href="{{ route('contact') }}">contact us</a> in the meantime.</p>
                @else
                    <div class="mt-2 grid gap-2">
                        @foreach ($documents as $doc)
                            <label class="choice items-start">
                                <input type="checkbox" name="documents[]" value="{{ $doc->id }}" class="mt-0.5" @checked(in_array((string) $doc->id, (array) old('documents', []), true) || (! old('documents') && ! $errors->any()))>
                                <span><span class="font-bold">{{ $doc->title }}</span><span class="block text-sm text-ink-2">{{ $doc->category->label() }}@if ($doc->description) · {{ $doc->description }}@endif</span></span>
                            </label>
                        @endforeach
                    </div>
                    <x-error for="documents" />
                @endif
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>02</b><span>Who it’s for</span></legend>
                <x-field name="company" label="Organisation" autocomplete="organization" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="contact_name" label="Your name" autocomplete="name" required />
                    <x-field name="job_title" label="Job title" optional autocomplete="organization-title" hint="e.g. Procurement officer, Contracts & HSE" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="email" label="Work email" type="email" autocomplete="email" required hint="The link is sent here. A company address speeds up approval." />
                    <x-field name="phone" label="Phone" type="tel" optional autocomplete="tel" />
                </div>
            </fieldset>

            <fieldset class="group space-y-5">
                <legend class="ptw"><b>03</b><span>Purpose</span></legend>
                <x-select name="purpose" label="What is it for?" :options="VendorRequest::PURPOSES" required />
                <x-field name="tender_reference" label="Tender, RFQ or vendor-registration reference" optional />
                <x-field name="message" label="Notes" type="textarea" optional rows="3" />
                <label class="choice items-start"><input type="checkbox" name="confirm" value="1" class="mt-0.5" @checked(old('confirm')) required><span>The documents are for my organisation’s procurement and compliance use only and will not be published.</span></label>
                <x-error for="confirm" />
            </fieldset>

            <button class="btn btn-blue">Request the pack<x-icon name="arrow-right" class="nudge h-4 w-4" /></button>
        </form>

        <aside class="space-y-5 lg:sticky lg:top-32 lg:self-start">
            <div class="tag-card">
                <div class="tag-card-head"><span class="ptw">How it works</span><x-icon name="lock" class="h-6 w-6 text-link" /></div>
                <ol class="steps space-y-5 p-5">
                    <li><strong>Request</strong><span class="block text-ink-2">You get a reference number by email.</span></li>
                    <li><strong>Approval</strong><span class="block text-ink-2">A Sheriff manager checks the request. Nothing is sent automatically.</span></li>
                    <li><strong>Private link</strong><span class="block text-ink-2">Signed, expires after {{ setting('vendor_link_days', '7') }} days, and can be withdrawn.</span></li>
                    <li><strong>Logged</strong><span class="block text-ink-2">Each download is recorded with date and time.</span></li>
                </ol>
            </div>
            <div class="sign">
                <span class="sign-disc"><x-icon name="shield" /></span>
                <div><p class="font-display font-semibold">ISO 9001:2015 · ISO 45001:2018</p><p class="text-sm text-ink-2">Summary on the <a class="u-link" href="{{ route('hse') }}">HSE &amp; compliance</a> page.</p></div>
            </div>
        </aside>
    </section>
</x-layouts.site>
