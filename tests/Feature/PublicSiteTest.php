<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function innerPages(): array
    {
        return collect(['/services', '/services/oil-and-gas', '/services/marine-and-port', '/industries', '/hse-and-compliance', '/group-companies',
            '/leadership', '/careers', '/careers/apply', '/request-a-proposal', '/vendor-prequalification', '/alarm-service', '/contact',
            '/privacy', '/photo-credits', '/sitemap'])->mapWithKeys(fn ($u) => [$u => [$u]])->all();
    }

    public function test_home_page_leads_with_the_sourced_positioning(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Security built for Guyana’s oil and gas era', false)
            ->assertSee('ISO 9001:2015')
            ->assertSee('ISO 45001:2018')
            ->assertSee('13 of the 26 accolades')
            ->assertSee('Request a proposal')
            ->assertSee('Get the vendor pack');
    }

    #[DataProvider('innerPages')]
    public function test_every_inner_page_uses_the_shared_page_header(string $url): void
    {
        $this->get($url)->assertOk()->assertSee('class="relative isolate overflow-hidden bg-brand-900 text-white on-dark"', false);
    }

    public function test_unknown_service_is_a_404(): void
    {
        $this->get('/services/k9-units')->assertNotFound();
    }

    public function test_no_client_is_claimed_without_a_source(): void
    {
        foreach (['/', '/industries', '/services/oil-and-gas', '/hse-and-compliance'] as $url) {
            $this->get($url)->assertDontSee('ExxonMobil')->assertDontSee('Exxon')->assertDontSee('Hess')->assertDontSee('SBM');
        }
    }

    public function test_preview_mode_is_noindex(): void
    {
        config(['sheriff.preview' => true]);
        $this->get('/')->assertSee('noindex, nofollow', false)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_contact_details_come_from_editable_settings(): void
    {
        $this->get('/contact')->assertSee('+592 227 5691');

        Setting::setMany(['phone' => '+592 600 1234', 'email' => '']);

        $this->get('/contact')->assertSee('+592 600 1234')->assertDontSee('+592 227 5691')->assertDontSee('sheriff.s.service@gmail.com');
    }

    public function test_every_photo_has_files_and_a_licence_credit(): void
    {
        foreach (array_keys(config('sheriff.photos')) as $key) {
            $this->assertArrayHasKey($key, config('photo-credits'), "{$key} has no credit");
            foreach ([480, 960, 1920] as $w) {
                $this->assertFileExists(public_path("img/{$key}-{$w}.webp"));
            }
        }
        $this->get('/photo-credits')->assertOk()->assertSee('Dan Lundberg')->assertSee('CC BY-SA 2.0');
    }

    public function test_every_service_cites_known_sources(): void
    {
        foreach (config('sheriff.services') as $service) {
            foreach ($service['source'] as $key) {
                $this->assertArrayHasKey($key, config('sheriff.sources'));
            }
        }
    }

    public function test_sitemap_lists_services_and_open_jobs_only(): void
    {
        $open = JobPosting::factory()->create(['slug' => 'marine-officer']);
        JobPosting::factory()->create(['slug' => 'secret-draft', 'status' => 'draft']);

        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('services.show', 'cash-in-transit'))
            ->assertSee(route('careers.show', $open))
            ->assertDontSee('secret-draft');
    }

    public function test_contact_form_stores_the_message_and_ignores_bots(): void
    {
        Notification::fake();

        $this->post('/contact', ['name' => 'Ann', 'email' => 'ann@example.com', 'topic' => 'general', 'body' => 'Hello'])
            ->assertRedirect('/contact')->assertSessionHas('sent');
        $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'topic' => 'general', 'body' => 'spam', 'website' => 'x']);

        $this->assertSame(1, Message::count());
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
