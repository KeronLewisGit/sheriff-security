<x-layouts.site title="Vendor pack" :mobile-bar="false">
    <x-page-header title="Vendor pack for {{ $vendor->company }}" tag="Approved" :no="$vendor->reference"
        lead="These documents are provided for {{ $vendor->company }}’s procurement and compliance use only. Each download is logged." />
    <section class="wrap mt-12 md:mt-16">
        <p class="hivis-note flex items-start gap-2 measure"><x-icon name="clock" class="mt-0.5 h-5 w-5 flex-none" /><span>This link expires on <strong>{{ $vendor->expires_at?->format('l j F Y, g:i a') }}</strong> (Guyana time).</span></p>
        <ul class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($vendor->documents as $doc)
                <li class="tag-card">
                    <div class="tag-card-head"><span class="coord text-ink-3">{{ $doc->category->label() }}</span><x-icon name="file" class="h-6 w-6 text-link" /></div>
                    <div class="p-5">
                        <h2 class="text-lg font-bold">{{ $doc->title }}</h2>
                        <p class="mt-1 text-sm text-ink-2">
                            @if ($doc->issued_on)Issued {{ $doc->issued_on->format('j M Y') }}@endif
                            @if ($doc->expires_on) · valid to {{ $doc->expires_on->format('j M Y') }}@endif
                            @if ($doc->size) · {{ human_bytes($doc->size) }}@endif
                        </p>
                        @if ($doc->hasFile())
                            <a href="{{ $vendor->downloadUrl($doc) }}" class="btn btn-blue btn-sm mt-4"><x-icon name="download" class="h-4 w-4" />Download</a>
                        @else
                            <p class="mt-4 text-sm text-ink-3">Being prepared. We will email it separately.</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.site>
