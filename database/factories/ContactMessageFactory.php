<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'contact' => fake()->safeEmail(),
            'topic' => fake()->randomElement(ContactMessage::TOPICS),
            'message' => fake()->paragraph(),
            'status' => 'new',
        ];
    }
}
