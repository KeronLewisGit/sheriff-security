@props(['name', 'label', 'options', 'hint' => null, 'optional' => false, 'value' => null, 'placeholder' => 'Choose…'])
@php
    $id = 'f-'.$name;
    $current = (string) old($name, $value);
@endphp
<div {{ $attributes->only('class') }}>
    <label class="label" for="{{ $id }}">{{ $label }}@if ($optional) <span class="opt">(optional)</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" class="input" @if($hint) aria-describedby="{{ $id }}-hint" @endif @error($name) aria-invalid="true" @enderror {{ $attributes->except('class') }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $v => $l)
            <option value="{{ $v }}" @selected($current === (string) $v)>{{ $l }}</option>
        @endforeach
    </select>
    @if ($hint)<span class="hint" id="{{ $id }}-hint">{{ $hint }}</span>@endif
    <x-error :for="$name" />
</div>
