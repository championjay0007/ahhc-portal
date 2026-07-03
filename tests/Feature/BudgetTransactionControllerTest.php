<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class BudgetTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_transaction_updates_budget()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $start = now()->startOfQuarter()->toDateString();
        $end = now()->endOfQuarter()->toDateString();

        $payload = ['participant_id' => $user->id];
        if (Schema::hasColumn('budgets', 'quarter_start')) {
            $payload['quarter_start'] = $start;
            $payload['quarter_end'] = $end;
        } else {
            $payload['quarter_start_date'] = $start;
            $payload['quarter_end_date'] = $end;
        }

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-'.strtoupper(Str::random(8)),
            'first_name' => 'Test',
            'last_name' => 'Participant',
            'status' => 'active',
            'phone' => '0400111222',
            'email' => $user->email,
        ]);

        $payload['participant_id'] = $participant->id;

        $budget = Budget::create(array_merge($payload, [
            'opening_budget' => 1000,
            'carry_over' => 0,
            'total_available' => 1000,
            'committed_funds' => 0,
            'pending_invoices' => 0,
            'approved_spend' => 0,
            'paid_spend' => 0,
            'remaining_balance' => 1000,
        ]));

        $resp = $this->post(route('budgets.transactions.store', $budget), [
            'type' => 'commitment',
            'amount' => 250.00,
        ]);

        $resp->assertRedirect();
        $budget->refresh();
        $this->assertEquals('250.00', number_format($budget->committed_funds, 2, '.', ''));
        $this->assertEquals('750.00', number_format($budget->remaining_balance, 2, '.', ''));
    }
}
