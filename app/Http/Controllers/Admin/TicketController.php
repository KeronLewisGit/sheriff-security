<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $status = TicketStatus::tryFrom(input_string($request->query('status')));
        $open = ! $status && $request->query('status') !== 'all';

        return view('admin.tickets.index', [
            'tickets' => ServiceTicket::query()
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($open, fn ($q) => $q->open())
                ->orderByDesc('urgent')->oldest()
                ->paginate(25)->withQueryString(),
            'status' => $status ? $status->value : ($open ? 'open' : 'all'),
            'counts' => ServiceTicket::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(ServiceTicket $ticket): View
    {
        return view('admin.tickets.show', ['ticket' => $ticket]);
    }

    public function update(Request $request, ServiceTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'visit_on' => ['nullable', 'date'],
            'public_note' => ['nullable', 'string', 'max:255'],
            'staff_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $ticket->moveTo(TicketStatus::from($data['status']));
        $ticket->fill(Arr::except($data, ['status']))->save();

        return back()->with('status', "{$ticket->reference} updated. The customer sees the new status and note on the lookup page.");
    }
}
