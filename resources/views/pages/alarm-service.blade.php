@php
    use App\Enums\TicketIssue;
    use App\Models\ServiceTicket;
    $issues = collect(TicketIssue::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    $line = setting('command_centre_line') ?: setting('phone');
@endphp
<x-layouts.site title="Alarm service & fault report" description="Sheriff Alarms customers: report an alarm fault or book a service visit, and check your ticket status online.">
    <x-page-header title="Alarm service & fault report" tag="Sheriff Alarms" no="SA" photo="cctv-wall" plate="Command centre · illustrative"
        lead="Report a fault or book a technician, get a ticket number, and check progress any time with that number and your email address." />

    <section class="wrap mt-10 md:mt-14">
        <div class="sign max-w-3xl" data-kind="warn" role="note">
            <span class="sign-disc"><x-icon name="alert" /></span>
            <div>
                <p class="font-display text-lg font-semibold">Break-in or alarm sounding now?</p>
                <p class="mt-1">Do not use this form. Call the police on <a class="font-bold underline" href="tel:911">911</a>@if ($line), then Sheriff on <a class="font-bold underline" href="{{ tel_href($line) }}">{{ format_phone($line) }}</a>@endif.</p>
            </div>
        </div>
    </section>

    <section class="wrap mt-10" x-data="{ tab: '{{ $tab === 'status' || $errors->has('reference') ? 'status' : 'report' }}' }">
        {{-- Without JavaScript both panels show one after the other; with it they become tabs. --}}
        <div class="hidden gap-2 border-b border-line [.js_&]:flex" role="tablist" aria-label="Alarm service">
            <button type="button" role="tab" class="-mb-px inline-flex min-h-12 items-center gap-2 border-b-[3px] px-4 font-bold" :class="tab === 'report' ? 'border-blue text-ink' : 'border-transparent text-ink-3'" :aria-selected="(tab === 'report').toString()" @click="tab = 'report'"><x-icon name="wrench" class="h-5 w-5" />Report a fault</button>
            <button type="button" role="tab" class="-mb-px inline-flex min-h-12 items-center gap-2 border-b-[3px] px-4 font-bold" :class="tab === 'status' ? 'border-blue text-ink' : 'border-transparent text-ink-3'" :aria-selected="(tab === 'status').toString()" @click="tab = 'status'"><x-icon name="search" class="h-5 w-5" />Check ticket status</button>
        </div>

        <div class="mt-8 grid gap-10 lg:grid-cols-[1.6fr_1fr] lg:gap-14">
            <div class="space-y-14">
                <form id="report" method="POST" action="{{ route('alarm-service.store') }}" class="space-y-8" novalidate x-show="tab === 'report'">
                    @csrf
                    <div class="hidden" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
                    <h2 class="display-3 [.js_&]:sr-only">Report a fault</h2>
                    <fieldset class="group space-y-5">
                        <legend class="ptw"><b>01</b><span>The problem</span></legend>
                        <x-select name="issue" label="What is happening?" :options="$issues" required />
                        <x-field name="description" label="Describe it" type="textarea" rows="4" required hint="Which zone or sensor, what the keypad shows, when it started." />
                        <label class="choice choice-urgent"><input type="checkbox" name="urgent" value="1" @checked(old('urgent'))><span><strong>Urgent:</strong> the system can’t be armed or the site is unprotected</span></label>
                    </fieldset>
                    <fieldset class="group space-y-5">
                        <legend class="ptw"><b>02</b><span>You and the site</span></legend>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-field name="name" label="Your name" autocomplete="name" required />
                            <x-field name="account_number" label="Alarm account number" optional hint="On your contract or invoice." />
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-field name="email" label="Email" type="email" autocomplete="email" required hint="You’ll need it to check the ticket." />
                            <x-field name="phone" label="Phone" type="tel" autocomplete="tel" required />
                        </div>
                        <x-field name="site_address" label="Site address" autocomplete="street-address" required />
                        <x-select name="preferred_time" label="Best time for a technician" :options="ServiceTicket::TIMES" optional placeholder="Any time" />
                    </fieldset>
                    <button class="btn btn-blue">Log the ticket<x-icon name="arrow-right" class="nudge h-4 w-4" /></button>
                </form>

                <form id="status" method="POST" action="{{ route('alarm-service.lookup') }}" class="space-y-5" novalidate x-show="tab === 'status'" @if ($tab !== 'status' && ! $errors->has('reference')) x-cloak @endif>
                    @csrf
                    <h2 class="display-3">Check ticket status</h2>
                    <p class="text-ink-2">Enter the ticket number from your confirmation email, with the email address you used.</p>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-field name="reference" id="lookup-reference" label="Ticket number" class="[&_input]:font-mono [&_input]:uppercase" placeholder="SA-{{ now()->format('y') }}-00000" autocomplete="off" required />
                        <x-field name="email" id="lookup-email" label="Email" type="email" autocomplete="email" required />
                    </div>
                    <button class="btn btn-primary"><x-icon name="search" class="h-4 w-4" />Check status</button>
                </form>
            </div>

            <aside class="space-y-5 lg:sticky lg:top-32 lg:self-start">
                <x-photo key="cctv-wall" alt="A wall of CCTV monitors in a control room" sizes="(min-width: 1024px) 30vw, 100vw" class="aspect-[16/9] rounded-md" plate="<b>24 H</b> Command centre" />
                <div class="panel p-5">
                    <h2 class="font-bold">Sheriff Alarms</h2>
                    <p class="mt-1 text-ink-2">Burglar alarm and intrusion systems, monitored from a 24-hour command centre. GAPSO 2024: Best Alarm Monitoring Services Provider and Best Alarm Installation Team.</p>
                    <x-source class="mt-2" :keys="['oilnow_2023', 'gapso_2024']" />
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
