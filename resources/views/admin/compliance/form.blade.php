@php use App\Enums\ComplianceCategory; @endphp
<x-layouts.admin :title="$document->exists ? 'Edit: '.$document->title : 'Add compliance document'">
    <form method="POST" action="{{ $document->exists ? route('admin.compliance.update', $document) : route('admin.compliance.store') }}" enctype="multipart/form-data" class="a-card max-w-2xl space-y-4 p-5">
        @csrf @if ($document->exists) @method('PUT') @endif
        <div><label class="a-label" for="title">Title</label><input id="title" name="title" class="a-input w-full" value="{{ old('title', $document->title) }}" required></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="a-label" for="category">Category</label><select id="category" name="category" class="a-input w-full">@foreach (ComplianceCategory::cases() as $c)<option value="{{ $c->value }}" @selected(old('category', $document->category?->value) === $c->value)>{{ $c->label() }}</option>@endforeach</select></div>
            <div><label class="a-label" for="sort">Order</label><input id="sort" name="sort" type="number" min="0" class="a-input w-full" value="{{ old('sort', $document->sort ?? 0) }}"></div>
        </div>
        <div><label class="a-label" for="description">Short description <span class="font-normal text-ink-3">(shown to requesters)</span></label><input id="description" name="description" class="a-input w-full" value="{{ old('description', $document->description) }}" maxlength="255"></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="a-label" for="issued_on">Issued</label><input id="issued_on" name="issued_on" type="date" class="a-input w-full" value="{{ old('issued_on', $document->issued_on?->toDateString()) }}"></div>
            <div><label class="a-label" for="expires_on">Valid to</label><input id="expires_on" name="expires_on" type="date" class="a-input w-full" value="{{ old('expires_on', $document->expires_on?->toDateString()) }}"></div>
        </div>
        <div><label class="a-label" for="file">File @if ($document->hasFile())<span class="font-normal text-ink-3">(current: {{ $document->original_name }}; choose a new file to replace it)</span>@endif</label><input id="file" name="file" type="file" class="a-input w-full" accept=".pdf,.jpg,.jpeg,.png,.docx"></div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_listed" value="1" class="h-4 w-4" @checked(old('is_listed', $document->is_listed))>List on the public request form</label>
        <div class="flex flex-wrap gap-2 pt-2">
            <button class="a-btn a-btn-primary">Save</button>
            <a href="{{ route('admin.compliance.index') }}" class="a-btn a-btn-ghost">Cancel</a>
        </div>
    </form>
    @if ($document->exists)
        <form method="POST" action="{{ route('admin.compliance.destroy', $document) }}" class="mt-4" onsubmit="return confirm('Delete this document and its file?')">@csrf @method('DELETE')<button class="a-btn a-btn-danger">Delete document</button></form>
    @endif
</x-layouts.admin>
