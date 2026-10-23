@props(['slug', 'service'])
<a href="{{ route('services.show', $slug) }}" {{ $attributes->merge(['class' => 'tag-card group h-full']) }}>
    <div class="tag-card-head">
        <span class="coord text-ink-3">{{ $service['tag'] }}</span>
        <x-icon :name="$service['icon']" class="h-6 w-6 text-link" />
    </div>
    <div class="flex flex-1 flex-col p-5 pt-4">
        <h3 class="text-xl font-semibold leading-snug">{{ $service['title'] }}</h3>
        <p class="mt-2 text-ink-2">{{ $service['summary'] }}</p>
        <span class="mt-auto inline-flex items-center gap-1.5 pt-5 font-bold text-link">Scope of service<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
    </div>
</a>
