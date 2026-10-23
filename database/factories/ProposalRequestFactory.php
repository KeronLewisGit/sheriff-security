<?php

namespace Database\Factories;

use App\Enums\Industry;
use App\Enums\ProposalStatus;
use App\Enums\SiteType;
use App\Models\ProposalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProposalRequest> */
class ProposalRequestFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'status' => ProposalStatus::New,
            'company' => fake()->company(),
            'contact_name' => fake()->name(),
            'job_title' => 'Procurement lead',
            'email' => fake()->companyEmail(),
            'phone' => '+592 '.fake()->numerify('### ####'),
            'industry' => fake()->randomElement(Industry::cases()),
            'site_type' => fake()->randomElement(SiteType::cases()),
            'site_location' => fake()->randomElement(['Houston, East Bank Demerara', 'Providence, EBD', 'Water Street, Georgetown', 'Eccles Industrial Estate']),
            'posts' => fake()->numberBetween(1, 12),
            'coverage' => '24_7',
            'armed' => fake()->randomElement(array_keys(ProposalRequest::ARMED)),
        ];
    }
}
