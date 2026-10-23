<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ProposalStatus;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\ComplianceDocument;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Message;
use App\Models\ProposalRequest;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Models\VendorRequest;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Leads with the one number to act on. For sales roles that is proposal requests
 * still unanswered past the response target; for HR it is applications waiting
 * more than two days.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $sales = $user->role->can('sales');
        $hiring = $user->role->can('hiring');
        $data = ['sales' => $sales, 'hiring' => $hiring, 'notifyInvalid' => Notify::invalid(), 'notifyMissing' => Notify::recipients() === []];

        if ($sales) {
            $overdue = ProposalRequest::overdue()->orderBy('created_at')->get();
            $weeks = collect(range(7, 0))->map(function (int $w) {
                $start = now()->startOfWeek()->subWeeks($w);

                return ['start' => $start, 'count' => ProposalRequest::whereBetween('created_at', [$start, $start->copy()->endOfWeek()])->count()];
            });
            $answered = ProposalRequest::whereNotNull('responded_at')->where('created_at', '>=', now()->subDays(90))->get(['created_at', 'responded_at']);
            $within = $answered->filter(fn (ProposalRequest $p) => $p->responded_at !== null && $p->created_at->diffInHours($p->responded_at) <= ProposalRequest::responseHours())->count();

            $data += [
                'overdue' => $overdue,
                'responseHours' => ProposalRequest::responseHours(),
                'newProposals' => ProposalRequest::where('status', ProposalStatus::New)->count(),
                'pipeline' => collect(ProposalStatus::cases())->map(fn (ProposalStatus $s) => ['status' => $s, 'count' => ProposalRequest::where('status', $s)->count()]),
                'weeks' => $weeks,
                'weeksMax' => max(1, (int) $weeks->max('count')),
                'onTimeRate' => $answered->count() ? (int) round($within / $answered->count() * 100) : null,
                'answeredCount' => $answered->count(),
                'vendorPending' => VendorRequest::where('status', VendorStatus::Pending)->count(),
                'ticketsOpen' => ServiceTicket::open()->count(),
                'ticketsUrgent' => ServiceTicket::open()->where('urgent', true)->count(),
                'messagesOpen' => Message::open()->count(),
                'docsExpiring' => ComplianceDocument::listed()->get()->filter(fn (ComplianceDocument $d) => $d->isExpired() || $d->expiresSoon())->values(),
                'docsMissingFile' => ComplianceDocument::where('is_listed', true)->whereNull('path')->count(),
            ];
        }

        if ($hiring) {
            $stale = JobApplication::where('status', ApplicationStatus::New)->where('created_at', '<=', now()->subDays(2));
            $oldest = (clone $stale)->min('created_at');
            $data += [
                'staleApplications' => $stale->count(),
                'oldestStale' => $oldest ? Carbon::parse($oldest) : null,
                'newApplications' => JobApplication::where('status', ApplicationStatus::New)->count(),
                'hiringPipeline' => collect(ApplicationStatus::cases())->map(fn (ApplicationStatus $s) => ['status' => $s, 'count' => JobApplication::where('status', $s)->count()]),
                'openJobs' => JobPosting::open()->count(),
                'recentApplications' => JobApplication::with('jobPosting')->latest()->limit(6)->get(),
            ];
        }

        return view('admin.dashboard', $data);
    }
}
