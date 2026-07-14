<?php

namespace Tests\Unit;

use App\Models\CareNote;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CareNoteTest extends TestCase
{
    protected function tearDown(): void
    {
        Schema::dropIfExists('care_notes');

        parent::tearDown();
    }

    public function test_it_populates_care_summary_from_tasks_completed_when_missing(): void
    {
        Schema::create('care_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('worker_id');
            $table->text('care_summary');
            $table->text('tasks_completed')->nullable();
            $table->text('observations')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        $note = CareNote::create([
            'participant_id' => 1,
            'worker_id' => 1,
            'tasks_completed' => 'Provided personal care and updated the support plan.',
            'observations' => 'Participant was calm and responsive.',
            'status' => 'submitted',
        ]);

        $this->assertSame('Provided personal care and updated the support plan.', $note->care_summary);
    }
}
