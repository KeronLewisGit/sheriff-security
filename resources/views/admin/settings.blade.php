<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="a-card max-w-3xl space-y-5 p-5">
        @csrf @method('PUT')
        <p class="text-ink-2">Contact details below came from Sheriff’s own website (last archived 2023–24) and match the Chamber of Commerce directory. Re-confirm them before launch. Clearing a field hides it on the website.</p>
        @foreach ($fields as $key => [$label, $hint])
            <div>
                <label class="a-label" for="s-{{ $key }}">{{ $label }}</label>
                <input id="s-{{ $key }}" name="{{ $key }}" class="a-input w-full" value="{{ old($key, setting($key)) }}" @if (in_array($key, ['rfp_response_hours', 'vendor_link_days'], true)) type="number" min="1" required @endif>
                @if ($hint)<p class="mt-1 text-sm text-ink-3">{{ $hint }}</p>@endif
            </div>
        @endforeach
        <button class="a-btn a-btn-primary">Save settings</button>
    </form>
</x-layouts.admin>
