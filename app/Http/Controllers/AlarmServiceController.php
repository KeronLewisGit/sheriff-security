<?php

namespace App\Http\Controllers;

use App\Enums\TicketIssue;
use App\Enums\TicketStatus;
use App\Models\ServiceTicket;
use App\Notifications\NewSubmission;
use App\Notifications\VisitorNotice;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlarmServiceController extends Controller
{
    public function create(Request $request): View
    {
        return view('pages.alarm-service', ['tab' => $request->query('tab') === 'status' ? 'status' : 'report']);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            $request->session()->put('ticket_reference', 'SA-'.now()->format('y').'-00000');

            return to_route('alarm-service.thanks');
        }

        $data = $request->validate([
            'issue' => ['required', Rule::enum(TicketIssue::class)],
            'urgent' => ['boolean'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'account_number' => ['nullable', 'string', 'max:40'],
            'site_address' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'preferred_time' => ['nullable', Rule::in(array_keys(ServiceTicket::TIMES))],
        ], ['phone.regex' => 'Enter a phone number our technician can call.']);

        $ticket = ServiceTicket::create([...$data, 'email' => strtolower($data['email']), 'urgent' => $request->boolean('urgent'), 'status' => TicketStatus::Received]);

        Notify::office(new NewSubmission(
            ($ticket->urgent ? 'URGENT alarm ticket ' : 'Alarm ticket ').$ticket->reference.': '.$ticket->issue->label(),
            [$ticket->name.' · '.$ticket->phone, $ticket->site_address, $ticket->description],
            route('admin.tickets.show', $ticket),
            $ticket->email,
            $ticket->name,
        ));
        Notify::visitor($ticket->email, new VisitorNotice(
            "Alarm service ticket {$ticket->reference}",
            'Hello '.$ticket->name.',',
            [
                'We have logged your report: '.$ticket->issue->label().'.',
                'Your ticket number is '.$ticket->reference.'. Use it with this email address to check progress on our website.',
            ],
            'Check ticket status',
            route('alarm-service', ['tab' => 'status']),
        ));

        $request->session()->put('ticket_reference', $ticket->reference);

        return to_route('alarm-service.thanks');
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        $reference = $request->session()->get('ticket_reference');

        return $reference ? view('pages.alarm-thanks', ['reference' => $reference]) : to_route('alarm-service');
    }

    public function lookup(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:20'],
            // Not "email": the fault-report form on the same page has its own email field.
            'lookup_email' => ['required', 'email', 'max:255'],
        ], [], ['lookup_email' => 'email']);

        $ticket = ServiceTicket::lookup($data['reference'], $data['lookup_email']);

        if (! $ticket) {
            // Same message whether the reference or the email was wrong, so neither can be probed.
            return to_route('alarm-service', ['tab' => 'status'])
                ->withInput()
                ->withErrors(['reference' => 'We could not find a ticket with that number and email address.']);
        }

        return view('pages.alarm-status', ['ticket' => $ticket]);
    }
}
