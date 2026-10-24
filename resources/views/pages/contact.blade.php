@php
    $phone = setting('phone');
    $topics = \App\Models\Message::TOPICS;
@endphp
<x-layouts.site title="Contact" description="Contact Sheriff Security Services, {{ setting('address_line') }}, {{ setting('address_city') }}, Guyana.">
    <x-page-header title="Contact Sheriff" tag="Head office" no="HQ" photo="cathedral" plate="Georgetown · 06°48′N 058°09′W"
        lead="For proposals, compliance documents and alarm faults, the dedicated forms are fastest: they go straight to the right team with a reference number." />

    <section class="wrap mt-12 grid gap-10 md:mt-16 lg:grid-cols-[1fr_1.3fr] lg:gap-16">
        <div class="space-y-6">
            <div class="grid gap-3">
                <a href="{{ route('rfp.create') }}" class="flex min-h-14 items-center gap-3 rounded-md border border-line bg-surface px-4 py-3 font-bold hover:border-blue"><x-icon name="file" class="h-5 w-5 text-link" />Request a proposal<x-icon name="arrow-right" class="ml-auto h-4 w-4" /></a>
                <a href="{{ route('vendor.create') }}" class="flex min-h-14 items-center gap-3 rounded-md border border-line bg-surface px-4 py-3 font-bold hover:border-blue"><x-icon name="clipboard" class="h-5 w-5 text-link" />Vendor prequalification pack<x-icon name="arrow-right" class="ml-auto h-4 w-4" /></a>
                <a href="{{ route('alarm-service') }}" class="flex min-h-14 items-center gap-3 rounded-md border border-line bg-surface px-4 py-3 font-bold hover:border-blue"><x-icon name="bell" class="h-5 w-5 text-link" />Alarm service &amp; fault report<x-icon name="arrow-right" class="ml-auto h-4 w-4" /></a>
                <a href="{{ route('careers') }}" class="flex min-h-14 items-center gap-3 rounded-md border border-line bg-surface px-4 py-3 font-bold hover:border-blue"><x-icon name="briefcase" class="h-5 w-5 text-link" />Jobs and applications<x-icon name="arrow-right" class="ml-auto h-4 w-4" /></a>
            </div>
            <dl class="panel space-y-4 p-6">
                <div><dt class="ptw">Address</dt><dd class="mt-1 text-lg">{{ setting('address_line') }}<br>{{ setting('address_city') }}, Guyana</dd></div>
                @if ($phone)<div><dt class="ptw">Phone</dt><dd class="flex flex-wrap items-center gap-x-2"><a class="inline-flex min-h-11 items-center text-lg font-bold text-link" href="{{ tel_href($phone) }}">{{ format_phone($phone) }}</a>@if (setting('phone_alt')) <span aria-hidden="true">·</span> <a class="u-link inline-flex min-h-11 items-center" href="{{ tel_href(setting('phone_alt')) }}">{{ format_phone(setting('phone_alt')) }}</a>@endif</dd></div>@endif
                @if (setting('whatsapp'))<div><dt class="ptw">WhatsApp</dt><dd><a class="u-link inline-flex min-h-11 items-center" href="https://wa.me/{{ preg_replace('/\D+/', '', setting('whatsapp')) }}">Message us on WhatsApp</a></dd></div>@endif
                @if (setting('email'))<div><dt class="ptw">Email</dt><dd><a class="inline-flex min-h-11 items-center break-all text-link" href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></dd></div>@endif
                @if (setting('office_hours'))<div><dt class="ptw">Office hours</dt><dd class="mt-1">{{ setting('office_hours') }}</dd></div>@endif
                <div><dt class="ptw">Map</dt><dd><a class="u-link inline-flex min-h-11 items-center" href="https://www.openstreetmap.org/?mlat={{ sheriff('geo.lat') }}&mlon={{ sheriff('geo.lng') }}#map=16/{{ sheriff('geo.lat') }}/{{ sheriff('geo.lng') }}" rel="noopener" target="_blank">Sheriff Street on OpenStreetMap</a></dd></div>
            </dl>
        </div>

        <div>
            <h2 class="display-3">Send a message</h2>
            @if (session('sent'))
                <div class="mt-5 flex gap-3 rounded-md border border-line bg-surface p-5" role="status"><x-icon name="check-circle" class="h-6 w-6 flex-none text-good" /><p><strong>Thank you.</strong> Your message has reached the office and someone will reply by email.</p></div>
            @endif
            <form method="POST" action="{{ route('contact.store') }}" class="mt-5 space-y-5" novalidate>
                @csrf
                <div class="hidden" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
                <x-select name="topic" label="What is it about?" :options="$topics" value="general" placeholder="Choose a topic" required />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="name" label="Your name" autocomplete="name" required />
                    <x-field name="company" label="Organisation" optional autocomplete="organization" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="email" label="Email" type="email" autocomplete="email" required />
                    <x-field name="phone" label="Phone" type="tel" optional autocomplete="tel" />
                </div>
                <x-field name="body" label="Message" type="textarea" rows="5" required />
                <button class="btn btn-primary">Send message<x-icon name="arrow-right" class="nudge h-4 w-4" /></button>
            </form>
        </div>
    </section>
</x-layouts.site>
