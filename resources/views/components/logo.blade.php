@props(['tone' => 'auto'])
{{--
    Placeholder mark in Sheriff's colours: a hexagon with a squared "S", echoing the
    group's hexagonal monogram. Swap for the client's own vector logo when supplied.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg viewBox="0 0 40 44" class="h-10 w-9 flex-none" aria-hidden="true">
        <path d="M20 1.5 38 11.5v21L20 42.5 2 32.5v-21z" fill="#3d5bd9"/>
        <path d="M20 6 34 13.8v16.4L20 38 6 30.2V13.8z" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="1"/>
        <path d="M27 14.5H16l-3 3v3.2l3 3h8l3 3v3.3l-3 3H13" fill="none" stroke="#fff" stroke-width="3.6" stroke-linejoin="bevel"/>
    </svg>
    <span class="leading-none {{ $tone === 'light' ? 'text-white' : 'text-ink' }}">
        <span class="font-display block text-[1.45rem] font-bold tracking-[0.06em]">SHERIFF</span>
        <span class="coord mt-1 block whitespace-nowrap text-[0.6rem] tracking-[0.16em] {{ $tone === 'light' ? 'text-sky' : 'text-ink-3' }}">Security Services</span>
    </span>
</span>
