<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminCareNoteViewTest extends TestCase
{
    public function test_admin_care_note_view_uses_safe_optional_time_rendering(): void
    {
        $viewPath = resource_path('views/admin/care_note.blade.php');
        $contents = file_get_contents($viewPath);

        $this->assertStringContainsString('$displayStartTime', $contents);
        $this->assertStringContainsString('$displayEndTime', $contents);
        $this->assertStringNotContainsString('optional($careNote->start_time)', $contents);
        $this->assertStringNotContainsString('optional($careNote->end_time)', $contents);
    }
}
