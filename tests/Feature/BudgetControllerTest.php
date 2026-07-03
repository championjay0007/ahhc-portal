<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_and_view_budget()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $start = now()->startOfQuarter()->toDateString();
        $end = now()->endOfQuarter()->toDateString();

        if (Schema::hasColumn('budgets', 'quarter_start')) {
            $data = [
                'quarter_start' => $start,
                'quarter_end' => $end,
                'opening_budget' => 1500.00,
                'carry_over' => 100.00,
            ];
        } else {
            $data = [
                'quarter_start_date' => $start,
                'quarter_end_date' => $end,
                'opening_budget' => 1500.00,
                'carry_over' => 100.00,
            ];
        }

        $resp = $this->post(route('budgets.store'), $data);
        $resp->assertRedirect();

        $budget = Budget::first();
        $this->assertNotNull($budget);
        $this->assertEquals(1500.00, (float) $budget->opening_budget);

        $show = $this->get(route('budgets.show', $budget));
        $show->assertStatus(200);
        $show->assertSee('Total Available');
    }
}
