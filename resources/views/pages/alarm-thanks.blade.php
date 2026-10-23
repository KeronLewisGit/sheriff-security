<x-layouts.site title="Ticket logged">
    <x-page-header title="Ticket logged" tag="Sheriff Alarms" no="SA" lead="Thank you. Our alarm team has your report and will call you to arrange the next step." />
    <section class="wrap mt-12 md:mt-16">
        <div class="tag-card max-w-xl">
            <div class="tag-card-head"><span class="ptw">Ticket number</span><x-icon name="check-circle" class="h-6 w-6 text-good" /></div>
            <div class="p-6">
                <p class="ref text-3xl text-link">{{ $reference }}</p>
                <p class="mt-3 text-ink-2">We have emailed you a copy. Use this number and your email address to check progress.</p>
                <a href="{{ route('alarm-service', ['tab' => 'status']) }}#status" class="btn btn-primary mt-5">Check status</a>
            </div>
        </div>
    </section>
</x-layouts.site>
