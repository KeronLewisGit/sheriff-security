<?php

namespace Database\Factories;

use App\Enums\VendorStatus;
use App\Models\VendorRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VendorRequest> */
class VendorRequestFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'status' => VendorStatus::Pending,
            'company' => fake()->company(),
            'contact_name' => fake()->name(),
            'job_title' => 'Supplier qualification analyst',
            'email' => fake()->companyEmail(),
            'purpose' => 'prequalification',
        ];
    }
}
