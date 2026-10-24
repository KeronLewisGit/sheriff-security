@props(['tone' => 'auto'])
{{--
    Sheriff Security Service's own logo (hexagonal "S" mark and wordmark), vectorised from the
    company's website artwork. Variants live in public/img/brand:
      sheriff-logo-on-dark.svg   periwinkle + white, for the navy header, footer and sidebar
      sheriff-logo-on-light.svg  Sheriff blue + ink, for light surfaces
      sheriff-logo.svg           the original blue + white colours; sheriff-logo-source.png is the file as published
    tone="light" means light artwork (on a dark band). "auto" follows the colour scheme.
--}}
@php($alt = 'Sheriff Security Service')
<span {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    @if ($tone === 'light')
        <img src="{{ asset('img/brand/sheriff-logo-on-dark.svg') }}" alt="{{ $alt }}" width="250" height="91" class="h-12 w-auto sm:h-[3.25rem]">
    @else
        <img src="{{ asset('img/brand/sheriff-logo-on-light.svg') }}" alt="{{ $alt }}" width="250" height="91" class="dark-hide h-12 w-auto sm:h-[3.25rem]">
        <img src="{{ asset('img/brand/sheriff-logo-on-dark.svg') }}" alt="{{ $alt }}" width="250" height="91" class="dark-show hidden h-12 w-auto sm:h-[3.25rem]">
    @endif
</span>
