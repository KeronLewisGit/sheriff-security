<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplianceCategory;
use App\Http\Controllers\Controller;
use App\Models\ComplianceDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceDocumentController extends Controller
{
    public function index(): View
    {
        return view('admin.compliance.index', ['documents' => ComplianceDocument::withCount('downloads')->orderBy('sort')->orderBy('title')->get()]);
    }

    public function create(): View
    {
        return view('admin.compliance.form', ['document' => new ComplianceDocument(['is_listed' => true, 'category' => ComplianceCategory::Certification])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $document = new ComplianceDocument;
        $this->save($request, $document);

        return to_route('admin.compliance.index')->with('status', "“{$document->title}” added.");
    }

    public function edit(ComplianceDocument $document): View
    {
        return view('admin.compliance.form', ['document' => $document]);
    }

    public function update(Request $request, ComplianceDocument $document): RedirectResponse
    {
        $this->save($request, $document);

        return to_route('admin.compliance.index')->with('status', "“{$document->title}” saved.");
    }

    public function destroy(ComplianceDocument $document): RedirectResponse
    {
        if ($document->path) {
            Storage::disk('local')->delete($document->path);
        }
        $document->delete();

        return to_route('admin.compliance.index')->with('status', 'Document removed.');
    }

    public function file(ComplianceDocument $document): StreamedResponse
    {
        abort_unless($document->hasFile() && Storage::disk('local')->exists((string) $document->path), 404);

        return Storage::disk('local')->download((string) $document->path, (string) $document->original_name);
    }

    private function save(Request $request, ComplianceDocument $document): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(ComplianceCategory::class)],
            'description' => ['nullable', 'string', 'max:255'],
            'issued_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
            'file' => ['nullable', 'file', 'max:15360', 'mimes:'.implode(',', ComplianceDocument::FILE_TYPES)],
        ]);
        $document->fill(Arr::except($data, ['file']));
        $document->sort = (int) ($data['sort'] ?? 0);
        $document->is_listed = $request->boolean('is_listed');

        if ($file = $request->file('file')) {
            if ($document->path) {
                Storage::disk('local')->delete($document->path);
            }
            $document->path = $file->store('compliance', 'local');
            $document->original_name = mb_substr($file->getClientOriginalName(), 0, 200);
            $document->size = (int) $file->getSize();
        }
        $document->save();
    }
}
