<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_sign_in_with_any_case_email(): void
    {
        $user = User::factory()->create(['email' => 'bd@sheriff.test', 'role' => 'bd']);

        $this->post('/login', ['email' => 'BD@sheriff.test', 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_refused(): void
    {
        User::factory()->create(['email' => 'hr@sheriff.test']);
        $this->post('/login', ['email' => 'hr@sheriff.test', 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_navigation_follows_the_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'bd']))->get('/admin')->assertOk()
            ->assertSee('Vendor packs')->assertDontSee('Pipeline')->assertDontSee('Users &amp; roles', false);
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin')->assertOk()
            ->assertSee('Pipeline')->assertDontSee('Vendor packs');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin')->assertOk()
            ->assertSee('Vendor packs')->assertSee('Pipeline')->assertSee('Settings');
    }

    public function test_only_admins_reach_settings_and_users(): void
    {
        foreach (['bd', 'hr'] as $role) {
            $u = User::factory()->create(['role' => $role]);
            $this->actingAs($u)->get('/admin/settings')->assertForbidden();
            $this->actingAs($u)->get('/admin/users')->assertForbidden();
        }
    }

    public function test_admin_updates_settings_and_the_site_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $fields = array_fill_keys(array_keys(Setting::FIELDS), '');

        $this->actingAs($admin)->put('/admin/settings', array_merge($fields, [
            'phone' => '+592 699 0000', 'address_line' => 'New HQ Road', 'address_city' => 'Georgetown',
            'rfp_response_hours' => '12', 'vendor_link_days' => '3',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')->assertSee('+592 699 0000')->assertSee('New HQ Road')->assertSee('reply within 12 hours');
        $this->assertSame('3', setting('vendor_link_days'));
    }

    public function test_settings_validate_urls_and_numbers(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put('/admin/settings', ['facebook' => 'javascript:alert(1)', 'rfp_response_hours' => '0', 'vendor_link_days' => '90'])
            ->assertSessionHasErrors(['facebook', 'rfp_response_hours', 'vendor_link_days']);
    }

    public function test_admin_manages_users_but_cannot_demote_or_delete_themself(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/users', ['name' => 'New HR', 'email' => 'NEW@sheriff.test', 'role' => 'hr', 'password' => 'long-enough-1'])->assertRedirect();
        $this->assertSame('hr', User::where('email', 'new@sheriff.test')->value('role')->value);

        $this->actingAs($admin)->patch("/admin/users/{$admin->id}", ['role' => 'hr'])->assertSessionHasErrors('role');
        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHasErrors('user');
        $this->assertSame('admin', $admin->fresh()->role->value);
    }

    public function test_create_admin_command_makes_an_administrator(): void
    {
        $this->artisan('app:create-admin', ['--name' => 'Owner', '--email' => 'owner@sheriff.test', '--password' => 'a-long-password'])->assertSuccessful();
        $this->assertSame('admin', User::where('email', 'owner@sheriff.test')->value('role')->value);
    }

    public function test_demo_seeder_creates_the_three_logins(): void
    {
        Storage::fake('local');
        $this->seed(DemoSeeder::class);

        foreach (['admin@sheriff.test' => 'admin', 'bd@sheriff.test' => 'bd', 'hr@sheriff.test' => 'hr'] as $email => $role) {
            $this->assertSame($role, User::where('email', $email)->value('role')->value);
        }
        $this->actingAs(User::where('email', 'admin@sheriff.test')->sole())->get('/admin')->assertOk()->assertSee('not answered within 24 hours');
    }
}
