<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\Experience;
use App\Enums\Region;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobApplication> */
class JobApplicationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'status' => ApplicationStatus::New,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => '6'.fake()->numerify('######'),
            'email' => fake()->safeEmail(),
            'region' => fake()->randomElement(Region::cases()),
            'experience' => fake()->randomElement(Experience::cases()),
            'armed_licence' => fake()->boolean(25),
            'offshore_ready' => fake()->boolean(40),
        ];
    }
}
