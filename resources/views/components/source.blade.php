@props(['keys'])
{{-- Small "Source:" line under a claim, so every fact on the site can be traced. --}}
@php($keys = (array) $keys)
<p {{ $attributes->merge(['class' => 'text-sm text-ink-3']) }}>Source{{ count($keys) > 1 ? 's' : '' }}:
    @foreach ($keys as $k)
        @php($s = source_link($k))
        <a href="{{ $s['url'] }}" class="underline decoration-1 underline-offset-2 hover:text-ink" rel="noopener" target="_blank">{{ $s['label'] }}</a>@if (! $loop->last); @endif
    @endforeach
</p>
