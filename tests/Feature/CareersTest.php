<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareersTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function valid(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Keisha', 'last_name' => 'Williams', 'phone' => '600-1234', 'region' => 'r4', 'experience' => 'forces',
            'police_clearance' => UploadedFile::fake()->image('pcc.jpg'),
            'identification' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf'),
            'consent' => '1',
        ], $overrides);
    }

    public function test_only_open_postings_are_listed(): void
    {
        $open = JobPosting::factory()->create(['title' => 'Marine patrol officer']);
        JobPosting::factory()->create(['title' => 'Secret draft', 'status' => 'draft']);
        JobPosting::factory()->create(['title' => 'Old job', 'closes_on' => now()->subDay()]);

        $this->get('/careers')->assertOk()->assertSee('Marine patrol officer')->assertDontSee('Secret draft')->assertDontSee('Old job');
        $this->get('/careers/'.$open->slug)->assertOk()->assertSee('JobPosting', false);
        $this->get('/careers/'.JobPosting::where('title', 'Secret draft')->value('slug'))->assertNotFound();
    }

    public function test_an_application_stores_documents_on_the_private_disk(): void
    {
        Storage::fake('local');
        Notification::fake();
        $job = JobPosting::factory()->create();

        $this->post('/careers/apply', $this->valid([
            'job_posting_id' => $job->id, 'armed_licence' => '1',
            'certificate' => [UploadedFile::fake()->create('first-aid.pdf', 50, 'application/pdf')],
        ]))->assertRedirect('/careers/apply/thanks');

        $a = JobApplication::with('documents')->sole();
        $this->assertSame(ApplicationStatus::New, $a->status);
        $this->assertTrue($a->armed_licence);
        $this->assertCount(3, $a->documents);
        $this->assertTrue($a->hasDocument(DocumentType::PoliceClearance));
        foreach ($a->documents as $d) {
            Storage::disk('local')->assertExists($d->path);
        }
        $this->get('/careers/apply/thanks')->assertSee($a->reference);
    }

    public function test_police_clearance_and_id_are_required(): void
    {
        $this->post('/careers/apply', $this->valid(['police_clearance' => null, 'identification' => null]))
            ->assertSessionHasErrors(['police_clearance', 'identification']);
        $this->post('/careers/apply', $this->valid(['police_clearance' => UploadedFile::fake()->create('virus.exe', 10)]))
            ->assertSessionHasErrors('police_clearance');
        $this->assertSame(0, JobApplication::count());
    }

    public function test_hr_moves_applicants_through_the_pipeline(): void
    {
        $a = JobApplication::factory()->create();
        $hr = User::factory()->create(['role' => 'hr']);

        $this->actingAs($hr)->get('/admin/applications/pipeline')->assertOk()->assertSee($a->full_name)->assertSee('Screening')->assertSee('Training');
        $this->actingAs($hr)->patchJson('/admin/applications/'.$a->id, ['status' => 'interview'])->assertOk()->assertJson(['label' => 'Interview']);
        $this->assertSame(ApplicationStatus::Interview, $a->fresh()->status);
        $this->assertNotNull($a->fresh()->status_changed_at);
    }

    public function test_documents_are_only_for_hr(): void
    {
        Storage::fake('local');
        $a = JobApplication::factory()->create();
        Storage::disk('local')->put('applications/1/pcc.pdf', 'x');
        $doc = $a->documents()->create(['type' => 'police_clearance', 'path' => 'applications/1/pcc.pdf', 'original_name' => 'pcc.pdf', 'size' => 1]);

        $this->get("/admin/applications/{$a->id}/documents/{$doc->id}")->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'bd']))->get("/admin/applications/{$a->id}/documents/{$doc->id}")->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get("/admin/applications/{$a->id}/documents/{$doc->id}")->assertOk();
    }

    public function test_csv_export_neutralises_spreadsheet_formulas(): void
    {
        JobApplication::factory()->create(['first_name' => '=HYPERLINK("x")', 'status' => ApplicationStatus::Training]);
        JobApplication::factory()->create(['first_name' => 'Plain', 'status' => ApplicationStatus::New]);

        $csv = $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin/applications/export?status=training')->assertOk()->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Plain', $csv);
        $this->assertStringContainsString('Police clearance', $csv);
    }

    public function test_hr_dashboard_leads_with_stale_applications(): void
    {
        JobApplication::factory()->count(2)->create(['created_at' => now()->subDays(3)]);
        JobApplication::factory()->create();

        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin')->assertOk()
            ->assertSeeInOrder(['2', 'applications waiting more than 2 days'])
            ->assertDontSee('proposal requests');
    }

    public function test_job_postings_can_be_created_by_hr_only(): void
    {
        $data = ['title' => 'Alarm technician', 'department' => 'Alarms', 'location' => 'Georgetown', 'employment_type' => 'full_time', 'status' => 'open', 'openings' => 2, 'summary' => 'Fix alarms'];

        $this->actingAs(User::factory()->create(['role' => 'bd']))->post('/admin/jobs', $data)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'hr']))->post('/admin/jobs', $data)->assertRedirect('/admin/jobs');
        $this->assertSame('alarm-technician', JobPosting::sole()->slug);
    }
}
