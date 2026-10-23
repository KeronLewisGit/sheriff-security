<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Enums\EmploymentType;
use App\Enums\Experience;
use App\Enums\Industry;
use App\Enums\JobStatus;
use App\Enums\ProposalStatus;
use App\Enums\Region;
use App\Enums\SiteType;
use App\Enums\TicketIssue;
use App\Enums\TicketStatus;
use App\Models\ComplianceDocument;
use App\Models\DocumentDownload;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Message;
use App\Models\ProposalRequest;
use App\Models\ServiceTicket;
use App\Models\User;
use App\Models\VendorRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Sample data for the pitch demo and local work:
 *   php artisan migrate:fresh --seeder=DemoSeeder
 * Every company, person and posting here is FICTIONAL demo content. Compliance files
 * are one-page placeholders marked "SAMPLE", not Sheriff's real certificates.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@sheriff.test'], ['name' => 'Demo Admin', 'password' => Hash::make('password'), 'role' => 'admin']);
        $bd = User::firstOrCreate(['email' => 'bd@sheriff.test'], ['name' => 'Demo Business Development', 'password' => Hash::make('password'), 'role' => 'bd']);
        User::firstOrCreate(['email' => 'hr@sheriff.test'], ['name' => 'Demo HR', 'password' => Hash::make('password'), 'role' => 'hr']);

        $this->call(ComplianceDocumentSeeder::class);
        $this->documents();
        $jobs = $this->jobs();
        $this->applications($jobs);
        $this->proposals($admin, $bd);
        $this->vendorRequests($bd);
        $this->tickets();
        $this->messages();
    }

    private function documents(): void
    {
        foreach (ComplianceDocument::all() as $doc) {
            if ($doc->hasFile()) {
                continue;
            }
            $path = 'compliance/sample-'.$doc->id.'.pdf';
            Storage::disk('local')->put($path, self::pdf('SAMPLE PLACEHOLDER - '.$doc->title.' - not a real document'));
            $doc->update([
                'path' => $path,
                'original_name' => str($doc->title)->slug().'-SAMPLE.pdf',
                'size' => Storage::disk('local')->size($path),
                'issued_on' => now()->subMonths(10)->startOfMonth(),
                // One expiring soon, so the dashboard reminder shows in the demo.
                'expires_on' => str_contains($doc->title, 'Insurance') ? now()->addDays(25) : now()->addMonths(14)->startOfMonth(),
            ]);
        }
    }

    /** @return list<JobPosting> */
    private function jobs(): array
    {
        $rows = [
            ['Security officer', 'Operations', 'Georgetown & East Bank Demerara', 12, 'Guard commercial and industrial sites on rotating shifts, controlling access and writing clear shift reports.',
                "Control access and check vehicles and visitors\nPatrol the site and log every round\nWrite shift and incident reports\nFollow site HSE rules and permit-to-work instructions",
                "Valid police clearance\nCXC or equivalent preferred\nAble to work day and night shifts\nGood written English"],
            ['Armed security officer', 'Cash in transit', 'Georgetown', 4, 'Crew armed cash-in-transit runs between client premises and banks.',
                "Escort cash and valuables on scheduled runs\nFollow route and radio procedures\nInspect vehicle and equipment each shift",
                "Valid firearm licence\nValid police clearance\nPrevious armed or forces experience"],
            ['Marine patrol officer', 'Marine', 'Demerara River', 3, 'Crew patrol vessels on the Demerara River supporting port and waterside clients.',
                "Crew patrol boats on scheduled and response patrols\nMonitor wharves and moored vessels\nMaintain logs and radio contact",
                "Comfortable working on the water\nValid police clearance\nBoat handling or coast guard experience an advantage"],
            ['Alarm technician', 'Alarms', 'Georgetown', 2, 'Install, service and repair intrusion alarm systems for Sheriff Alarms customers.',
                "Install panels, sensors and keypads\nAttend fault tickets and record the fix\nTest systems with the command centre",
                "Low-voltage electrical experience\nValid driver’s licence\nValid police clearance"],
        ];
        $jobs = [];
        foreach ($rows as $i => [$title, $dept, $loc, $openings, $summary, $duties, $req]) {
            $jobs[] = JobPosting::updateOrCreate(['slug' => str($title)->slug()->toString()], [
                'title' => $title, 'department' => $dept, 'location' => $loc, 'employment_type' => EmploymentType::FullTime,
                'status' => JobStatus::Open, 'openings' => $openings, 'summary' => $summary, 'duties' => $duties, 'requirements' => $req,
                'closes_on' => now()->addWeeks(4 + $i)->toDateString(),
            ]);
        }
        JobPosting::updateOrCreate(['slug' => 'shift-supervisor'], [
            'title' => 'Shift supervisor', 'department' => 'Operations', 'location' => 'Georgetown', 'employment_type' => EmploymentType::FullTime,
            'status' => JobStatus::Draft, 'openings' => 1, 'summary' => 'Draft posting: lead a shift of officers across several client sites.',
        ]);

        return $jobs;
    }

    /** @param list<JobPosting> $jobs */
    private function applications(array $jobs): void
    {
        if (JobApplication::query()->exists()) {
            return;
        }
        $people = [
            ['Devon', 'Persaud', ApplicationStatus::New, 1], ['Keisha', 'Williams', ApplicationStatus::New, 3], ['Ravi', 'Singh', ApplicationStatus::New, 4],
            ['Andre', 'Thomas', ApplicationStatus::New, 0], ['Shondell', 'Garrett', ApplicationStatus::Screening, 6], ['Mohan', 'Ramsaroop', ApplicationStatus::Screening, 8],
            ['Tiffany', 'Adams', ApplicationStatus::Interview, 10], ['Collin', 'Fraser', ApplicationStatus::Interview, 12], ['Nadira', 'Khan', ApplicationStatus::Training, 18],
            ['Jermaine', 'Benn', ApplicationStatus::Training, 20], ['Latoya', 'Bovell', ApplicationStatus::Hired, 30], ['Sean', 'DaSilva', ApplicationStatus::Rejected, 25],
        ];
        foreach ($people as $i => [$first, $last, $status, $daysAgo]) {
            $at = now()->subDays($daysAgo)->subHours($i);
            $app = JobApplication::factory()->create([
                'first_name' => $first, 'last_name' => $last, 'status' => $status, 'email' => strtolower($first.'.'.$last).'@example.com',
                'job_posting_id' => $i % 5 === 4 ? null : $jobs[$i % count($jobs)]->id,
                'region' => [Region::R4, Region::R4, Region::R3, Region::R6, Region::R5, Region::R10][$i % 6],
                'experience' => [Experience::Forces, Experience::TwoToFive, Experience::None, Experience::UnderTwo][$i % 4],
                'status_changed_at' => $status === ApplicationStatus::New ? null : $at->copy()->addDays(1),
                'created_at' => $at, 'updated_at' => $at,
            ]);
            foreach ([DocumentType::PoliceClearance, DocumentType::Identification] as $type) {
                $path = 'applications/'.$app->id.'/'.$type->value.'.pdf';
                Storage::disk('local')->put($path, self::pdf('SAMPLE '.$type->label().' - '.$app->full_name));
                $app->documents()->create(['type' => $type, 'path' => $path, 'original_name' => $type->value.'.pdf', 'size' => Storage::disk('local')->size($path)]);
            }
        }
    }

    private function proposals(User $admin, User $bd): void
    {
        if (ProposalRequest::query()->exists()) {
            return;
        }
        $rows = [
            // [company, industry, site type, location, posts, armed, status, hours ago]
            ['Demo Energy Services Inc.', Industry::OilGas, SiteType::Shorebase, 'Supply base, East Bank Demerara', 8, 'mixed', ProposalStatus::New, 41],
            ['Demo Marine Logistics', Industry::Maritime, SiteType::Port, 'Wharf, Water Street, Georgetown', 4, 'armed', ProposalStatus::New, 30],
            ['Demo Subsea Contractors', Industry::OilGas, SiteType::Onshore, 'Pipe yard, Region 3', 6, 'unarmed', ProposalStatus::New, 27],
            ['Demo Hotels Guyana', Industry::Commercial, SiteType::Urban, 'Kingston, Georgetown', 3, 'unarmed', ProposalStatus::New, 5],
            ['Demo Trading Co.', Industry::Financial, SiteType::Multiple, 'Branches in Regions 4 and 6', 2, 'armed', ProposalStatus::Qualified, 70],
            ['Demo Offshore Catering', Industry::OilGas, SiteType::Offshore, 'Vessel support, Georgetown harbour', 2, 'advise', ProposalStatus::SiteSurvey, 120],
            ['Demo Embassy Residence', Industry::Diplomatic, SiteType::Urban, 'Bel Air Park, Georgetown', 2, 'unarmed', ProposalStatus::ProposalSent, 200],
            ['Demo Fabrication Yard', Industry::OilGas, SiteType::Onshore, 'Industrial estate, Region 4', 10, 'mixed', ProposalStatus::ProposalSent, 300],
            ['Demo Retail Group', Industry::Commercial, SiteType::Multiple, 'Malls in Georgetown', 6, 'unarmed', ProposalStatus::Won, 600],
            ['Demo Telecoms Ltd', Industry::Commercial, SiteType::Multiple, 'Tower sites, coastland', 5, 'unarmed', ProposalStatus::Lost, 900],
        ];
        foreach ($rows as $i => [$company, $industry, $site, $loc, $posts, $armed, $status, $hours]) {
            $at = now()->subHours($hours);
            $p = ProposalRequest::factory()->create([
                'company' => $company, 'industry' => $industry, 'site_type' => $site, 'site_location' => $loc, 'posts' => $posts, 'armed' => $armed,
                'contact_name' => ['Alicia Ramnarine', 'Marcus Joseph', 'Priya Doobay', 'Kevin Hope', 'Sasha Benjamin'][$i % 5],
                'email' => 'procurement'.($i + 1).'@example.com', 'status' => ProposalStatus::New,
                'details' => $i === 0 ? "24-hour access control and patrol at the base gate and laydown yard.\nContractors must hold site induction; permit-to-work applies in the yard." : null,
                'start_date' => now()->addWeeks(3 + $i)->toDateString(), 'contract_term' => '1y',
                'created_at' => $at, 'updated_at' => $at,
            ]);
            if ($status !== ProposalStatus::New) {
                $p->forceFill([
                    'status' => $status, 'owner_id' => $i % 2 ? $bd->id : $admin->id,
                    'responded_at' => $at->copy()->addHours($i % 3 === 0 ? 30 : 6), 'status_changed_at' => $at->copy()->addDays(2),
                ])->save();
            }
        }
    }

    private function vendorRequests(User $bd): void
    {
        if (VendorRequest::query()->exists()) {
            return;
        }
        $docs = ComplianceDocument::listed()->pluck('id');
        $pending = VendorRequest::factory()->create(['company' => 'Demo Energy Services Inc.', 'contact_name' => 'Alicia Ramnarine', 'email' => 'supplier.qualification@example.com', 'purpose' => 'tender', 'tender_reference' => 'DEMO-RFQ-2026-118', 'created_at' => now()->subHours(20)]);
        $pending->documents()->sync($docs->take(5));
        $pending2 = VendorRequest::factory()->create(['company' => 'Demo Drilling Support', 'contact_name' => 'Ken Lall', 'email' => 'ken.lall@gmail.com', 'created_at' => now()->subHours(3)]);
        $pending2->documents()->sync($docs->take(2));

        $approved = VendorRequest::factory()->create(['company' => 'Demo Marine Logistics', 'contact_name' => 'Marcus Joseph', 'email' => 'contracts@example.com', 'purpose' => 'prequalification', 'created_at' => now()->subDays(3)]);
        $approved->documents()->sync($docs);
        $approved->approve($bd, 7);
        foreach ($approved->documents->take(3) as $i => $d) {
            DocumentDownload::create(['vendor_request_id' => $approved->id, 'compliance_document_id' => $d->id, 'document_title' => $d->title, 'ip' => '190.80.12.'.(20 + $i), 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0']);
        }

        $declined = VendorRequest::factory()->create(['company' => 'Unknown Ventures', 'contact_name' => 'J. Smith', 'email' => 'jsmith123@yahoo.com', 'purpose' => 'other', 'created_at' => now()->subDays(6)]);
        $declined->documents()->sync($docs->take(1));
        $declined->decline($bd, 'Could not verify the organisation.');
    }

    private function tickets(): void
    {
        if (ServiceTicket::query()->exists()) {
            return;
        }
        $rows = [
            [TicketIssue::NoSignal, true, TicketStatus::Received, 2, null],
            [TicketIssue::FalseAlarms, false, TicketStatus::Scheduled, 26, 'Technician booked for tomorrow morning.'],
            [TicketIssue::Battery, false, TicketStatus::InProgress, 50, null],
            [TicketIssue::NewInstall, false, TicketStatus::Received, 6, null],
            [TicketIssue::Keypad, false, TicketStatus::Resolved, 120, 'Keypad replaced and system tested with the command centre.'],
        ];
        foreach ($rows as $i => [$issue, $urgent, $status, $hours, $note]) {
            $at = now()->subHours($hours);
            $t = ServiceTicket::factory()->create(['issue' => $issue, 'urgent' => $urgent, 'name' => ['Rohan Mohamed', 'Celina Fung', 'Dwayne Rodney', 'Anita Narine', 'Paul Grant'][$i], 'email' => 'customer'.($i + 1).'@example.com', 'created_at' => $at, 'updated_at' => $at]);
            $t->moveTo($status);
            $t->public_note = $note;
            $t->visit_on = $status === TicketStatus::Scheduled ? Carbon::tomorrow() : null;
            $t->save();
        }
    }

    private function messages(): void
    {
        if (Message::query()->exists()) {
            return;
        }
        Message::create(['name' => 'Demo Journalist', 'email' => 'news@example.com', 'topic' => 'media', 'body' => 'Requesting a comment on the security sector awards for a feature article.']);
        Message::create(['name' => 'Demo Supplier', 'email' => 'sales@example.com', 'company' => 'Demo Uniforms', 'topic' => 'supplier', 'body' => 'We supply uniforms and boots and would like to quote.']);
    }

    /** A minimal one-page PDF with a single line of text, for placeholder files. */
    public static function pdf(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $stream = "BT /F1 14 Tf 60 760 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
