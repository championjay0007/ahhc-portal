<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worker>
 */
class WorkerFactory extends Factory
{
    protected $model = Worker::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'worker_number' => 'W-'.$this->faker->unique()->numerify('####'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'role_type' => 'carer',
            'status' => 'active',
            'qualification' => null,
            'availability' => null,
            'compliance_expiry_at' => now()->addMonths(6),
            'background_check_expiry_at' => now()->addMonths(12),
            'vehicle_type' => null,
            'notes' => null,
            'onboarding_stage' => 6,
        ];
    }
}
