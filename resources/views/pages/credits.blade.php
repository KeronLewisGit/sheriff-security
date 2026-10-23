@php($credits = config('photo-credits'))
<x-layouts.site title="Photo credits">
    <x-page-header title="Photo credits" tag="Licences" no="IMG" lead="Every photo on this concept site is from Wikimedia Commons and is used under the licence shown. They illustrate Guyana and the sectors Sheriff works in; none shows Sheriff’s own people or equipment." />
    <section class="wrap mt-12 md:mt-16">
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (sheriff('photos') as $key => $photo)
                @php($c = $credits[$key] ?? null)
                @continue(! $c)
                <li class="panel overflow-hidden">
                    <x-photo :key="$key" :alt="\Illuminate\Support\Str::beforeLast($c['title'], '.')" sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw" class="aspect-[3/2]" />
                    <div class="p-5 text-sm">
                        <p class="font-bold"><a class="u-link" href="{{ $c['source'] }}" rel="noopener" target="_blank">{{ \Illuminate\Support\Str::beforeLast($c['title'], '.') }}</a></p>
                        <p class="mt-1 text-ink-2">By {{ $c['author'] }} ·
                            @if ($c['license_url'])<a class="u-link" href="{{ $c['license_url'] }}" rel="license noopener" target="_blank">{{ $c['license'] }}</a>@else{{ $c['license'] }}@endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="mt-8 text-sm text-ink-3 measure">CC BY-SA images are shown unmodified apart from resizing. These are placeholders: at launch they should be replaced with Sheriff’s own photography of its officers, vessels and command centre.</p>
    </section>
</x-layouts.site>
