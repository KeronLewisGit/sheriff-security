<?php

namespace Database\Factories;

use App\Enums\TicketIssue;
use App\Enums\TicketStatus;
use App\Models\ServiceTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceTicket> */
class ServiceTicketFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'status' => TicketStatus::Received,
            'issue' => fake()->randomElement(TicketIssue::cases()),
            'urgent' => false,
            'name' => fake()->name(),
            'email' => strtolower(fake()->safeEmail()),
            'phone' => '6'.fake()->numerify('######'),
            'site_address' => fake()->streetAddress().', Georgetown',
            'description' => fake()->sentence(12),
        ];
    }
}
