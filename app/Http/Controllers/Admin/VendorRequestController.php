<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorRequest;
use App\Notifications\VisitorNotice;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = VendorStatus::tryFrom(input_string($request->query('status')));

        return view('admin.vendor.index', [
            'requests' => VendorRequest::withCount(['documents', 'downloads'])
                ->when($status, fn ($q) => $q->where('status', $status))
                ->orderByRaw("case when status = 'pending' then 0 else 1 end")->latest()
                ->paginate(25)->withQueryString(),
            'status' => $status?->value,
            'counts' => VendorRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(VendorRequest $vendorRequest): View
    {
        return view('admin.vendor.show', ['vr' => $vendorRequest->load(['documents', 'downloads', 'decider'])]);
    }

    public function approve(Request $request, VendorRequest $vendorRequest): RedirectResponse
    {
        abort_if($vendorRequest->documents()->whereNull('path')->exists() && ! $request->boolean('allow_missing'), 422, 'One of the requested documents has no file uploaded yet.');
        /** @var User $user */
        $user = $request->user();
        $days = max(1, min(30, (int) setting('vendor_link_days', '7')));
        $vendorRequest->approve($user, $days);

        Notify::visitor($vendorRequest->email, new VisitorNotice(
            "Your Sheriff Security vendor pack ({$vendorRequest->reference})",
            'Hello '.$vendorRequest->contact_name.',',
            [
                'Your request for our compliance documents has been approved.',
                'The link below is private to '.$vendorRequest->company.' and works until '.$vendorRequest->expires_at?->format('j F Y, g:i a').' (Guyana time). Each download is logged.',
            ],
            'Open the vendor pack',
            $vendorRequest->packUrl(),
            'If you need more time or other documents, reply to this email quoting '.$vendorRequest->reference.'.',
        ));

        return back()->with('status', "Approved. The link was emailed to {$vendorRequest->email} and expires in {$days} days.");
    }

    public function decline(Request $request, VendorRequest $vendorRequest): RedirectResponse
    {
        $data = $request->validate(['decision_note' => ['nullable', 'string', 'max:255']]);
        /** @var User $user */
        $user = $request->user();
        $vendorRequest->decline($user, $data['decision_note'] ?? null);

        return back()->with('status', "{$vendorRequest->reference} declined. No email was sent.");
    }

    public function revoke(VendorRequest $vendorRequest): RedirectResponse
    {
        $vendorRequest->revoke();

        return back()->with('status', "Link for {$vendorRequest->reference} withdrawn. It stops working immediately.");
    }
}
