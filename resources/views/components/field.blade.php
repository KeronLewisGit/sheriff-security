@props(['name', 'label', 'type' => 'text', 'hint' => null, 'optional' => false, 'value' => null, 'id' => null])
{{-- Labelled input with hint and error wired up through aria-describedby. --}}
@php
    $id ??= 'f-'.str_replace(['.', '[', ']'], '-', $name);
    // name="documents[]" reports array-level errors (too many files) under "documents".
    $key = str_ends_with($name, '[]') ? substr($name, 0, -2) : $name;
    $describedBy = trim(($hint ? $id.'-hint ' : '').($errors->has($key) ? str_replace(['.', '[', ']'], '-', $key).'-error' : ''));
@endphp
<div {{ $attributes->only('class') }}>
    <label class="label" for="{{ $id }}">{{ $label }}@if ($optional) <span class="opt">(optional)</span>@endif</label>
    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" class="input" @if($describedBy) aria-describedby="{{ $describedBy }}" @endif @error($key) aria-invalid="true" @enderror {{ $attributes->except('class') }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'file' ? '' : old($name, $value) }}" class="input" @if($describedBy) aria-describedby="{{ $describedBy }}" @endif @error($key) aria-invalid="true" @enderror {{ $attributes->except('class') }}>
    @endif
    @if ($hint)<span class="hint" id="{{ $id }}-hint">{{ $hint }}</span>@endif
    <x-error :for="$key" />
</div>
