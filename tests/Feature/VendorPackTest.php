<?php

namespace Tests\Feature;

use App\Enums\VendorStatus;
use App\Models\ComplianceDocument;
use App\Models\DocumentDownload;
use App\Models\User;
use App\Models\VendorRequest;
use App\Notifications\VisitorNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VendorPackTest extends TestCase
{
    use RefreshDatabase;

    private ComplianceDocument $iso;

    private ComplianceDocument $insurance;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::disk('local')->put('compliance/iso.pdf', 'ISO');
        Storage::disk('local')->put('compliance/ins.pdf', 'INS');
        $this->iso = ComplianceDocument::create(['title' => 'ISO 9001:2015 certificate', 'category' => 'certification', 'path' => 'compliance/iso.pdf', 'original_name' => 'iso.pdf', 'size' => 3]);
        $this->insurance = ComplianceDocument::create(['title' => 'Insurance certificate', 'category' => 'insurance', 'path' => 'compliance/ins.pdf', 'original_name' => 'ins.pdf', 'size' => 3]);
    }

    private function approved(): VendorRequest
    {
        $vr = VendorRequest::factory()->create();
        $vr->documents()->sync([$this->iso->id]);
        $vr->approve(User::factory()->create(['role' => 'bd']), 7);

        return $vr->fresh();
    }

    public function test_the_public_form_lists_documents_and_creates_a_pending_request(): void
    {
        Notification::fake();
        ComplianceDocument::create(['title' => 'Hidden internal doc', 'category' => 'other', 'is_listed' => false]);

        $this->get('/vendor-prequalification')->assertOk()->assertSee('ISO 9001:2015 certificate')->assertDontSee('Hidden internal doc');

        $this->post('/vendor-prequalification', [
            'company' => 'Demo Energy', 'contact_name' => 'Alicia', 'email' => 'alicia@example.com', 'purpose' => 'tender',
            'documents' => [$this->iso->id, $this->insurance->id], 'confirm' => '1',
        ])->assertRedirect('/vendor-prequalification/thanks');

        $vr = VendorRequest::sole();
        $this->assertSame(VendorStatus::Pending, $vr->status);
        $this->assertNull($vr->access_token);
        $this->assertCount(2, $vr->documents);
        $this->get('/vendor-prequalification/thanks')->assertSee($vr->reference);
    }

    public function test_hidden_documents_and_missing_confirmation_are_rejected(): void
    {
        $hidden = ComplianceDocument::create(['title' => 'Hidden', 'category' => 'other', 'is_listed' => false]);

        $this->post('/vendor-prequalification', ['company' => 'X', 'contact_name' => 'Y', 'email' => 'y@example.com', 'purpose' => 'tender', 'documents' => [$hidden->id]])
            ->assertSessionHasErrors(['documents.0', 'confirm']);
        $this->assertSame(0, VendorRequest::count());
    }

    public function test_approval_emails_a_signed_link_that_downloads_and_logs(): void
    {
        Notification::fake();
        $vr = VendorRequest::factory()->create(['email' => 'buyer@example.com']);
        $vr->documents()->sync([$this->iso->id]);

        $this->actingAs(User::factory()->create(['role' => 'bd']))->post("/admin/vendor-requests/{$vr->id}/approve")->assertRedirect();

        $vr->refresh();
        $this->assertSame(VendorStatus::Approved, $vr->status);
        $this->assertTrue($vr->expires_at->between(now()->addDays(6), now()->addDays(8)));

        $url = null;
        Notification::assertSentTo(new AnonymousNotifiable, VisitorNotice::class, function (VisitorNotice $n, $c, $notifiable) use (&$url) {
            $url = $n->actionUrl;

            return $notifiable->routes['mail'] === 'buyer@example.com';
        });
        $this->assertStringContainsString('signature=', (string) $url);

        auth()->logout();
        $this->get((string) $url)->assertOk()->assertSee('ISO 9001:2015 certificate')->assertDontSee('Insurance certificate');

        $this->get((string) $vr->downloadUrl($this->iso))->assertOk()->assertDownload('Sheriff-Security-iso-90012015-certificate.pdf');
        $log = DocumentDownload::sole();
        $this->assertSame($vr->id, $log->vendor_request_id);
        $this->assertSame('ISO 9001:2015 certificate', $log->document_title);
        $this->assertNotNull($log->ip);
    }

    public function test_an_unsigned_or_tampered_link_is_refused(): void
    {
        $vr = $this->approved();

        $this->get('/vendor-pack/'.$vr->access_token)->assertForbidden();
        $this->get(str_replace($this->iso->id.'?', $this->insurance->id.'?', (string) $vr->downloadUrl($this->iso)))->assertForbidden();
        $this->assertSame(0, DocumentDownload::count());
    }

    public function test_the_link_stops_working_when_it_expires(): void
    {
        $vr = $this->approved();
        $url = (string) $vr->packUrl();
        $download = (string) $vr->downloadUrl($this->iso);

        $this->get($url)->assertOk();

        $this->travel(8)->days();

        $this->get($url)->assertForbidden();
        $this->get($download)->assertForbidden();
        $this->assertSame(0, DocumentDownload::count());
    }

    public function test_a_revoked_link_is_gone_even_with_a_valid_signature(): void
    {
        $vr = $this->approved();
        $url = (string) $vr->packUrl();

        $this->actingAs(User::factory()->create(['role' => 'bd']))->post("/admin/vendor-requests/{$vr->id}/revoke")->assertRedirect();
        auth()->logout();

        $this->get($url)->assertStatus(410)->assertSee('This link has expired');
        $this->assertSame(VendorStatus::Revoked, $vr->fresh()->status);
    }

    public function test_a_document_outside_the_approved_pack_cannot_be_downloaded(): void
    {
        $vr = $this->approved();
        $url = URL::temporarySignedRoute('vendor-pack.download', $vr->expires_at, ['token' => $vr->access_token, 'document' => $this->insurance->id]);

        $this->get($url)->assertNotFound();
    }

    public function test_declined_requests_never_get_a_link(): void
    {
        Notification::fake();
        $vr = VendorRequest::factory()->create();
        $vr->documents()->sync([$this->iso->id]);

        $this->actingAs(User::factory()->create(['role' => 'bd']))->post("/admin/vendor-requests/{$vr->id}/decline", ['decision_note' => 'Unverified'])->assertRedirect();

        $vr->refresh();
        $this->assertSame(VendorStatus::Declined, $vr->status);
        $this->assertNull($vr->packUrl());
        Notification::assertNothingSent();
    }

    public function test_approval_is_restricted_to_sales_roles(): void
    {
        $vr = VendorRequest::factory()->create();
        $vr->documents()->sync([$this->iso->id]);

        $this->post("/admin/vendor-requests/{$vr->id}/approve")->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'hr']))->post("/admin/vendor-requests/{$vr->id}/approve")->assertForbidden();
        $this->assertSame(VendorStatus::Pending, $vr->fresh()->status);

        $this->actingAs(User::factory()->create(['role' => 'admin']))->post("/admin/vendor-requests/{$vr->id}/approve")->assertRedirect();
        $this->assertSame(VendorStatus::Approved, $vr->fresh()->status);
    }

    public function test_approval_warns_when_a_requested_document_has_no_file(): void
    {
        $empty = ComplianceDocument::create(['title' => 'HSE policy statement', 'category' => 'hse']);
        $vr = VendorRequest::factory()->create();
        $vr->documents()->sync([$empty->id]);
        $bd = User::factory()->create(['role' => 'bd']);

        $this->actingAs($bd)->post("/admin/vendor-requests/{$vr->id}/approve")->assertRedirect()->assertSessionHasErrors('decision');
        $this->assertSame(VendorStatus::Pending, $vr->fresh()->status);
        $this->actingAs($bd)->post("/admin/vendor-requests/{$vr->id}/approve", ['allow_missing' => 1])->assertRedirect()->assertSessionHasNoErrors();
    }

    public function test_a_second_approve_keeps_the_emailed_link_working(): void
    {
        Notification::fake();
        $vr = VendorRequest::factory()->create();
        $vr->documents()->sync([$this->iso->id]);
        $bd = User::factory()->create(['role' => 'bd']);

        $this->actingAs($bd)->post("/admin/vendor-requests/{$vr->id}/approve")->assertSessionHasNoErrors();
        $link = $vr->fresh()->packUrl();
        $this->actingAs($bd)->post("/admin/vendor-requests/{$vr->id}/approve")->assertSessionHasErrors('decision');

        $this->get((string) $link)->assertOk();
        Notification::assertSentTimes(VisitorNotice::class, 1);
    }

    public function test_decided_requests_cannot_change_state_again(): void
    {
        $bd = User::factory()->create(['role' => 'bd']);
        $declined = VendorRequest::factory()->create();
        $declined->decline($bd, null);

        $this->actingAs($bd)->post("/admin/vendor-requests/{$declined->id}/approve")->assertSessionHasErrors('decision');
        $this->actingAs($bd)->post("/admin/vendor-requests/{$declined->id}/revoke")->assertSessionHasErrors('decision');
        $this->assertSame(VendorStatus::Declined, $declined->fresh()->status);

        $approved = $this->approved();
        $approved->revoke();
        $this->actingAs($bd)->post("/admin/vendor-requests/{$approved->id}/decline")->assertSessionHasErrors('decision');
        $this->assertSame(VendorStatus::Revoked, $approved->fresh()->status);
    }

    public function test_compliance_files_are_private_and_uploadable_by_sales(): void
    {
        $bd = User::factory()->create(['role' => 'bd']);
        $this->assertFalse(file_exists(public_path('compliance/iso.pdf')));

        $this->actingAs($bd)->post('/admin/compliance', [
            'title' => 'NIS compliance certificate', 'category' => 'registration', 'is_listed' => '1',
            'file' => UploadedFile::fake()->create('nis.pdf', 50, 'application/pdf'), 'expires_on' => now()->addYear()->toDateString(),
        ])->assertRedirect('/admin/compliance');

        $doc = ComplianceDocument::where('title', 'NIS compliance certificate')->sole();
        Storage::disk('local')->assertExists((string) $doc->path);
        $this->actingAs($bd)->get("/admin/compliance/{$doc->id}/file")->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get("/admin/compliance/{$doc->id}/file")->assertForbidden();
    }
}
