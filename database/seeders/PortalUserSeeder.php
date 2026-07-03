<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;

class PortalUserSeeder extends Seeder
{
    public function run(): void
    {
        // Preserve existing data by using updateOrCreate for all seeded records.
        $users = [
            [
                'name' => 'Participant User',
                'email' => 'participant@example.com',
                'role' => 'participant',
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Worker User',
                'email' => 'worker@example.com',
                'role' => 'worker',
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Portal Admin',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@ahhc.com.au',
                'role' => 'admin',
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => 'Password123!',
            ],
            [
                'name' => 'John Davies',
                'email' => 'john.davies@ahhc.com.au',
                'role' => 'admin',
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => 'Password123!',
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@ahhc.com.au',
                'role' => 'admin',
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => 'Password123!',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'status' => $userData['status'],
                    'mfa_enabled' => $userData['mfa_enabled'],
                    'password' => $userData['password'],
                    'password_changed_at' => now(),
                    'email_verified_at' => now(),
                ]
            );

            [$firstName, $lastName] = $this->splitFullName($user->name);

            if ($user->role === 'participant') {
                Participant::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'participant_number' => 'P-'.$user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'status' => 'active',
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'consent_to_share' => false,
                        'budget_limit_cents' => 0,
                        'current_budget_used_cents' => 0,
                        'created_by_id' => $user->id,
                        'updated_by_id' => $user->id,
                    ]
                );
            }

            if ($user->role === 'worker') {
                Worker::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'worker_number' => 'W-'.$user->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'role_type' => 'worker',
                        'status' => 'active',
                    ]
                );
            }
        }

        // Create test participants in various workflow states without deleting any existing records.
        $this->createTestParticipants();
    }

    private function createTestParticipants(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $testParticipants = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
                'participant_number' => 'P-1001',
                'status' => Participant::STATUS_PENDING_ADMIN_REVIEW,
                'notes' => 'Awaiting admin review after onboarding submission',
            ],
            [
                'name' => 'Bob Martinez',
                'email' => 'bob.martinez@example.com',
                'participant_number' => 'P-1002',
                'status' => Participant::STATUS_AHHC_REVIEW,
                'notes' => 'In AHHC assessment phase',
            ],
            [
                'name' => 'Claire Thompson',
                'email' => 'claire.thompson@example.com',
                'participant_number' => 'P-1003',
                'status' => Participant::STATUS_ELIGIBILITY_ASSESSMENT,
                'notes' => 'Undergoing eligibility assessment',
            ],
            [
                'name' => 'David Chen',
                'email' => 'david.chen@example.com',
                'participant_number' => 'P-1004',
                'status' => Participant::STATUS_SUITABILITY_ASSESSMENT,
                'notes' => 'Undergoing suitability assessment',
            ],
            [
                'name' => 'Emma Wilson',
                'email' => 'emma.wilson@example.com',
                'participant_number' => 'P-1005',
                'status' => Participant::STATUS_ACTIVE,
                'notes' => 'Actively enrolled',
            ],
        ];

        foreach ($testParticipants as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => 'participant',
                    'status' => 'active',
                    'mfa_enabled' => false,
                    'password' => bcrypt('Password123!'),
                    'password_changed_at' => now(),
                    'email_verified_at' => now(),
                ]
            );

            [$firstName, $lastName] = $this->splitFullName($user->name);

            Participant::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'participant_number' => $data['participant_number'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'status' => $data['status'],
                    'phone' => '0400000000',
                    'email' => $user->email,
                    'consent_to_share' => true,
                    'budget_limit_cents' => 500000, // $5000
                    'current_budget_used_cents' => 0,
                    'notes' => $data['notes'],
                    'created_by_id' => $admin->id,
                    'updated_by_id' => $admin->id,
                ]
            );
        }
    }

    private function splitFullName(string $fullName): array
    {
        $trimmedName = trim($fullName);

        if ($trimmedName === '') {
            return ['User', 'User'];
        }

        $parts = preg_split('/\s+/', $trimmedName);
        $firstName = $parts[0] ?? 'User';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'User';

        return [$firstName, $lastName];
    }
}
