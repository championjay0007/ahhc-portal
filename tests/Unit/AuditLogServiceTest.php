<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_safely_nulls_user_id_when_referenced_user_does_not_exist(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();

        $auditLog = AuditLogService::record('Failed Login', null, ['email' => 'participant@example.com'], [], $userId);

        $this->assertNull($auditLog->user_id);
        $this->assertSame('Failed Login', $auditLog->action);
    }
}
