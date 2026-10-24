<?php

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Models\ProposalRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewSubmission;
use App\Notifications\VisitorNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProposalRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function valid(array $overrides = []): array
    {
        return array_merge([
            'company' => 'Demo Energy Services', 'contact_name' => 'Alicia R', 'email' => 'alicia@example.com', 'phone' => '+592 600 1234',
            'industry' => 'oil_gas', 'site_type' => 'shorebase', 'site_location' => 'East Bank Demerara', 'posts' => 6,
            'coverage' => '24_7', 'armed' => 'mixed', 'start_date' => now()->addMonth()->toDateString(),
        ], $overrides);
    }

    public function test_a_proposal_request_with_documents_is_stored_privately_and_confirmed(): void
    {
        Storage::fake('local');
        Notification::fake();
        Setting::setMany(['notify_email' => 'bd@sheriff.test']);

        $this->post('/request-a-proposal', $this->valid(['documents' => [UploadedFile::fake()->create('scope.pdf', 300, 'application/pdf')]]))
            ->assertRedirect('/request-a-proposal/thanks');

        $p = ProposalRequest::sole();
        $this->assertMatchesRegularExpression('/^RFP-\d{2}-\d{5}$/', $p->reference);
        $this->assertSame(ProposalStatus::New, $p->status);
        $this->assertSame('shorebase', $p->site_type->value);
        Storage::disk('local')->assertExists($p->documents->first()->path);
        $this->assertStringStartsWith('proposals/', $p->documents->first()->path);

        $this->get('/request-a-proposal/thanks')->assertSee($p->reference);
        Notification::assertSentTo(new AnonymousNotifiable, NewSubmission::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === ['bd@sheriff.test']);
        Notification::assertSentTo(new AnonymousNotifiable, VisitorNotice::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'alicia@example.com');
    }

    public function test_validation_rejects_bad_input(): void
    {
        $this->post('/request-a-proposal', $this->valid(['posts' => 0, 'site_type' => 'moon', 'start_date' => '2020-01-01']))
            ->assertSessionHasErrors(['posts', 'site_type', 'start_date']);
        $this->post('/request-a-proposal', $this->valid(['documents' => [UploadedFile::fake()->create('x.exe', 10)]]))
            ->assertSessionHasErrors('documents.0');
        $this->assertSame(0, ProposalRequest::count());
    }

    public function test_too_many_documents_is_shown_on_the_documents_field(): void
    {
        Storage::fake('local');
        $files = array_map(fn (int $i) => UploadedFile::fake()->create("scope{$i}.pdf", 10, 'application/pdf'), range(1, 6));

        $this->from('/request-a-proposal')->followingRedirects()->post('/request-a-proposal', $this->valid(['documents' => $files]))
            ->assertSee('id="documents-error"', false)->assertSee('Attach up to 5 documents.');
        $this->assertSame(0, ProposalRequest::count());
    }

    public function test_requests_past_the_response_target_are_overdue_until_someone_responds(): void
    {
        $old = ProposalRequest::factory()->create(['created_at' => now()->subHours(30)]);
        $fresh = ProposalRequest::factory()->create(['created_at' => now()->subHours(2)]);

        $this->assertTrue($old->isOverdue());
        $this->assertFalse($fresh->isOverdue());
        $this->assertSame([$old->id], ProposalRequest::overdue()->pluck('id')->all());

        $old->moveTo(ProposalStatus::Qualified);
        $old->save();
        $this->assertNotNull($old->responded_at);
        $this->assertSame(0, ProposalRequest::overdue()->count());

        // The target is an editable setting.
        Setting::setMany(['rfp_response_hours' => '1']);
        $this->assertSame([$fresh->id], ProposalRequest::overdue()->pluck('id')->all());
    }

    public function test_dashboard_counts_requests_per_week_and_per_stage(): void
    {
        ProposalRequest::factory()->count(2)->create(['created_at' => now()->startOfWeek()->addHour()]);
        ProposalRequest::factory()->create(['created_at' => now()->startOfWeek()->subWeeks(2)->addDay(), 'status' => ProposalStatus::Won]);
        ProposalRequest::factory()->create(['created_at' => now()->subWeeks(12)]);

        $this->actingAs(User::factory()->create(['role' => 'bd']))->get('/admin')->assertOk()
            ->assertSee('<tr><td>'.now()->startOfWeek()->format('j M Y').'</td><td>2</td></tr>', false)
            ->assertSee('<tr><td>'.now()->startOfWeek()->subWeeks(2)->format('j M Y').'</td><td>1</td></tr>', false)
            ->assertSee('<tr><td>'.now()->startOfWeek()->subWeek()->format('j M Y').'</td><td>0</td></tr>', false);
    }

    public function test_dashboard_leads_with_the_overdue_count(): void
    {
        ProposalRequest::factory()->count(2)->create(['created_at' => now()->subHours(48)]);
        ProposalRequest::factory()->create();

        $this->actingAs(User::factory()->create(['role' => 'bd']))->get('/admin')->assertOk()
            ->assertSeeInOrder(['2', 'proposal requests', 'not answered within 24 hours'])
            ->assertSee('Work the overdue list');
    }

    public function test_board_moves_a_card_by_json_and_records_the_response(): void
    {
        $p = ProposalRequest::factory()->create(['created_at' => now()->subDays(2)]);
        $bd = User::factory()->create(['role' => 'bd']);

        $this->actingAs($bd)->get('/admin/proposals/board')->assertOk()->assertSee($p->company);
        $this->actingAs($bd)->patchJson('/admin/proposals/'.$p->id, ['status' => 'site_survey'])
            ->assertOk()->assertJson(['label' => 'Site survey']);

        $p->refresh();
        $this->assertSame(ProposalStatus::SiteSurvey, $p->status);
        $this->assertNotNull($p->responded_at);
    }

    public function test_hr_cannot_see_proposals_and_guests_are_sent_to_login(): void
    {
        $p = ProposalRequest::factory()->create();

        $this->get('/admin/proposals')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin/proposals')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'hr']))->patch('/admin/proposals/'.$p->id, ['status' => 'won'])->assertForbidden();
    }

    public function test_attached_documents_download_only_for_their_own_request(): void
    {
        Storage::fake('local');
        $a = ProposalRequest::factory()->create();
        $b = ProposalRequest::factory()->create();
        Storage::disk('local')->put('proposals/1/scope.pdf', 'x');
        $doc = $a->documents()->create(['path' => 'proposals/1/scope.pdf', 'original_name' => 'scope.pdf', 'size' => 1]);
        $bd = User::factory()->create(['role' => 'bd']);

        $this->actingAs($bd)->get("/admin/proposals/{$a->id}/documents/{$doc->id}")->assertOk()->assertDownload('scope.pdf');
        $this->actingAs($bd)->get("/admin/proposals/{$b->id}/documents/{$doc->id}")->assertNotFound();
    }
}
