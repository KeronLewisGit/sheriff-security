@props(['title', 'lead' => null, 'tag' => null, 'no' => null, 'photo' => null, 'plate' => null])
{{--
    The one hero band every inner page uses, so titles start at the same height and
    left edge site-wide. Indigo with a chart graticule; with a photo, the photo sits
    behind a scrim. Breadcrumbs, actions or chips go in the slot.
--}}
<section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-brand-900 text-white on-dark']) }}>
    @if ($photo)
        <div class="hero-photo absolute inset-0 -z-20">
            <x-photo :key="$photo" alt="" :eager="true" sizes="100vw" class="h-full w-full" />
        </div>
        <div class="hero-scrim absolute inset-0 -z-10"></div>
    @endif
    <div class="graticule absolute inset-0 -z-10" aria-hidden="true"></div>
    <div class="wrap pb-12 pt-10 md:pb-16 md:pt-16 lg:pb-20 lg:pt-20">
        @if ($tag)<x-ptw :no="$no">{{ $tag }}</x-ptw>@endif
        <h1 class="display-2 mt-4 max-w-4xl">{{ $title }}</h1>
        @if ($lead)<p class="lead mt-4 text-white/85">{{ $lead }}</p>@endif
        {{ $slot }}
    </div>
    <p class="coord absolute bottom-3 right-4 hidden text-white/60 md:block lg:right-8" aria-hidden="true">{{ $plate ?? '06°48′N · 058°09′W · Georgetown' }}</p>
</section>
