@props(['no' => null])
{{-- Permit-to-work tag eyebrow: eyelet, tag number, label. --}}
<p {{ $attributes->merge(['class' => 'ptw']) }}>@if ($no)<b>{{ $no }}</b>@endif<span>{{ $slot }}</span></p>
