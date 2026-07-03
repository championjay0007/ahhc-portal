<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'participant_number' => 'P-'.$this->faker->unique()->numerify('####'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'status' => Participant::STATUS_ACTIVE,
            'email' => $user->email,
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postcode' => $this->faker->postcode(),
            'consent_to_share' => true,
            'budget_limit_cents' => 0,
            'current_budget_used_cents' => 0,
        ];
    }
}
