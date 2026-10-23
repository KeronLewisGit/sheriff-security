<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<JobPosting> */
class JobPostingFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->randomElement(['Security officer', 'Armed security officer', 'Marine patrol officer', 'Alarm technician', 'Shift supervisor']);

        return [
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'title' => $title,
            'department' => 'Operations',
            'location' => 'Georgetown',
            'employment_type' => EmploymentType::FullTime,
            'status' => JobStatus::Open,
            'openings' => fake()->numberBetween(1, 10),
            'summary' => fake()->sentence(14),
            'duties' => "Patrol assigned posts\nComplete shift reports",
            'requirements' => "Valid police clearance\nAble to work shifts",
            'closes_on' => null,
        ];
    }
}
