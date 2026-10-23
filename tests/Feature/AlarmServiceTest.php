<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AlarmServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fault_report_gets_a_ticket_number(): void
    {
        Notification::fake();

        $this->post('/alarm-service', [
            'issue' => 'no_signal', 'urgent' => '1', 'name' => 'Rohan', 'email' => 'Rohan@Example.com', 'phone' => '600 1234',
            'site_address' => 'Lot 5 Main Street', 'description' => 'Panel offline since last night.',
        ])->assertRedirect('/alarm-service/thanks');

        $t = ServiceTicket::sole();
        $this->assertMatchesRegularExpression('/^SA-\d{2}-\d{5}$/', $t->reference);
        $this->assertTrue($t->urgent);
        $this->assertSame('rohan@example.com', $t->email);
        $this->get('/alarm-service/thanks')->assertSee($t->reference);
    }

    public function test_status_lookup_needs_the_matching_email(): void
    {
        $t = ServiceTicket::factory()->create(['email' => 'paul@example.com', 'public_note' => 'Technician booked', 'staff_notes' => 'Customer was rude']);

        $this->post('/alarm-service/status', ['reference' => strtolower($t->reference), 'email' => 'PAUL@example.com'])
            ->assertOk()->assertSee($t->reference)->assertSee('Technician booked')->assertDontSee('Customer was rude');

        $this->post('/alarm-service/status', ['reference' => $t->reference, 'email' => 'someone@example.com'])
            ->assertRedirect()->assertSessionHasErrors(['reference' => 'We could not find a ticket with that number and email address.']);
        $this->post('/alarm-service/status', ['reference' => 'SA-26-99999', 'email' => 'paul@example.com'])
            ->assertSessionHasErrors('reference');
    }

    public function test_lookups_are_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/alarm-service/status', ['reference' => 'SA-26-0000'.$i, 'email' => 'x@example.com']);
        }
        $this->post('/alarm-service/status', ['reference' => 'SA-26-00011', 'email' => 'x@example.com'])->assertStatus(429);
    }

    public function test_staff_updates_show_up_on_the_customer_lookup(): void
    {
        $t = ServiceTicket::factory()->create(['email' => 'c@example.com']);
        $bd = User::factory()->create(['role' => 'bd']);

        $this->actingAs($bd)->patch('/admin/alarm-tickets/'.$t->id, ['status' => 'scheduled', 'visit_on' => now()->addDay()->toDateString(), 'public_note' => 'Visit booked for 9 am'])->assertRedirect();
        $this->assertSame(TicketStatus::Scheduled, $t->fresh()->status);

        auth()->logout();
        $this->post('/alarm-service/status', ['reference' => $t->reference, 'email' => 'c@example.com'])
            ->assertSee('Technician scheduled')->assertSee('Visit booked for 9 am');

        $this->actingAs($bd)->patch('/admin/alarm-tickets/'.$t->id, ['status' => 'resolved']);
        $this->assertNotNull($t->fresh()->resolved_at);
    }

    public function test_hr_cannot_open_alarm_tickets(): void
    {
        $t = ServiceTicket::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin/alarm-tickets/'.$t->id)->assertForbidden();
    }
}
