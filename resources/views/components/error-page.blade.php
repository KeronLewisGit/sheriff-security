@props(['code'])
@php
    $copy = [
        '403' => ['Not available', 'You do not have access to this page.'],
        '404' => ['Page not found', 'The page may have moved. Everything Sheriff offers is one click from the home page.'],
        '410' => ['This link has expired', 'Vendor pack links work for a limited time and can be withdrawn. Request the pack again and our team will send a fresh link.'],
        '419' => ['Your session timed out', 'For your security the form expired. Go back, refresh the page and try again.'],
        '429' => ['Too many attempts', 'Please wait a minute and try again.'],
        '500' => ['Something went wrong', 'The error has been logged. Please try again shortly or call us.'],
        '503' => ['Back shortly', 'The website is being updated. Please try again in a few minutes.'],
    ][$code] ?? ['Error', ''];
    // Signed vendor-pack links answer 403 when expired or edited: say so plainly.
    if ($code === '403' && request()->is('vendor-pack/*')) {
        [$copy, $code] = [['This link has expired', 'Vendor pack links work for a limited time and cannot be edited. Request the pack again and our team will send a fresh link.'], '410'];
    }
@endphp
<x-layouts.site :title="$copy[0]" :mobile-bar="false">
    <x-page-header :title="$copy[0]" :lead="$copy[1]" tag="Status" :no="'E-'.$code" />
    <section class="wrap mt-12 flex flex-wrap gap-3 md:mt-16">
        @if ($code === '410')
            <a href="{{ route('vendor.create') }}" class="btn btn-blue">Request the vendor pack<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        @endif
        <a href="{{ route('home') }}" class="btn btn-primary">Home page</a>
        @if (setting('phone'))<a href="{{ tel_href(setting('phone')) }}" class="btn btn-outline"><x-icon name="phone" class="h-4 w-4" />{{ format_phone(setting('phone')) }}</a>@endif
    </section>
</x-layouts.site>
