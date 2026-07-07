<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_view_budget()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();

        $this->actingAs($user);

        $start = now()->startOfQuarter()->toDateString();
        $end = now()->endOfQuarter()->toDateString();

        if (Schema::hasColumn('budgets', 'quarter_start')) {
            $data = [
                'participant_id' => $participant->id,
                'quarter_start' => $start,
                'quarter_end' => $end,
                'opening_budget' => 1500.00,
                'carry_over' => 100.00,
            ];
        } else {
            $data = [
                'participant_id' => $participant->id,
                'quarter_start_date' => $start,
                'quarter_end_date' => $end,
                'opening_budget' => 1500.00,
                'carry_over' => 100.00,
            ];
        }

        $resp = $this->post(route('budgets.store'), $data);
        $resp->assertRedirect();
        $resp->assertSessionHasNoErrors();

        $budget = Budget::first();
        $this->assertNotNull($budget);
        $this->assertEquals(1500.00, (float) $budget->opening_budget);
        $this->assertEquals($participant->id, $budget->participant_id);

        $show = $this->get(route('budgets.show', $budget));
        $show->assertStatus(200);
        $show->assertSee('Total Available');
    }

    public function test_admin_can_edit_budget()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_budget' => 1000.00,
            'carry_over' => 50.00,
        ]);

        $this->actingAs($user);

        $edit = $this->get(route('budgets.edit', $budget));
        $edit->assertStatus(200);
        $edit->assertSee('Edit Budget');

        $resp = $this->put(route('budgets.update', $budget), [
            'opening_budget' => 2200.00,
            'carry_over' => 120.00,
        ]);

        $resp->assertRedirect(route('budgets.show', $budget));
        $resp->assertSessionHas('status', 'Budget updated successfully.');

        $budget->refresh();
        $this->assertEquals(2200.00, (float) $budget->opening_budget);
        $this->assertEquals(120.00, (float) $budget->carry_over);
    }

    public function test_admin_budget_list_view_opens_budget_page()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_budget' => 1000.00,
            'carry_over' => 50.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portal.admin.budgets'));
        $response->assertStatus(200);
        $response->assertSee(route('budgets.show', $budget));
    }

    public function test_admin_can_delete_budget()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_budget' => 1000.00,
            'carry_over' => 50.00,
        ]);

        $this->actingAs($user);

        $resp = $this->delete(route('budgets.destroy', $budget));
        $resp->assertRedirect(route('budgets.index'));
        $resp->assertSessionHas('status', 'Budget deleted successfully.');

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }
}
