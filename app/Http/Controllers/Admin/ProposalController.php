<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Industry;
use App\Enums\ProposalStatus;
use App\Http\Controllers\Controller;
use App\Models\ProposalDocument;
use App\Models\ProposalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProposalController extends Controller
{
    public function index(Request $request): View
    {
        $status = ProposalStatus::tryFrom(input_string($request->query('status')));
        $industry = Industry::tryFrom(input_string($request->query('industry')));
        $q = input_string($request->query('q'));
        $overdue = $request->boolean('overdue');

        $query = ProposalRequest::with('owner')
            ->when($status, fn (Builder $b) => $b->where('status', $status))
            ->when($industry, fn (Builder $b) => $b->where('industry', $industry))
            ->when($overdue, fn (Builder $b) => $b->overdue())
            ->when($q !== '', function (Builder $b) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $b->where(fn (Builder $w) => $w->where('company', 'like', $like)->orWhere('reference', 'like', $like)->orWhere('contact_name', 'like', $like)->orWhere('site_location', 'like', $like));
            });

        return view('admin.proposals.index', [
            'proposals' => $query->latest()->paginate(25)->withQueryString(),
            'counts' => ProposalRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'filters' => ['status' => $status?->value, 'industry' => $industry?->value, 'q' => $q, 'overdue' => $overdue],
        ]);
    }

    public function board(): View
    {
        $all = ProposalRequest::with('owner')->latest()->get();

        return view('admin.proposals.board', [
            'columns' => collect(ProposalStatus::cases())->mapWithKeys(fn (ProposalStatus $s) => [$s->value => $all->where('status', $s)->values()]),
        ]);
    }

    public function show(ProposalRequest $proposal): View
    {
        return view('admin.proposals.show', [
            'proposal' => $proposal->load(['documents', 'owner']),
            'staff' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, ProposalRequest $proposal): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::enum(ProposalStatus::class)],
            'owner_id' => ['sometimes', 'nullable', Rule::exists('users', 'id')],
            'value_estimate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999'],
            'staff_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);

        if (isset($data['status'])) {
            $proposal->moveTo(ProposalStatus::from($data['status']));
        }
        $proposal->fill(Arr::except($data, ['status']))->save();

        if ($request->wantsJson()) {
            return response()->json(['label' => $proposal->status->label(), 'status' => $proposal->status->value]);
        }

        return back()->with('status', "{$proposal->reference} saved.");
    }

    public function document(ProposalRequest $proposal, ProposalDocument $document): StreamedResponse
    {
        abort_unless($document->proposal_request_id === $proposal->id && Storage::disk('local')->exists($document->path), 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }
}
