<?php

namespace App\Http\Controllers;

use App\Models\BackupRecord;
use App\Models\PermissionGroup;
use App\Models\PortalSetting;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SystemAdminController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $roleBreakdown = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();

        $permissionGroupsCount = PermissionGroup::count();
        $backupSummary = $this->backupSummary();
        $healthMetrics = $this->systemHealthMetrics();

        return view('admin.system.dashboard', compact(
            'usersCount',
            'roleBreakdown',
            'permissionGroupsCount',
            'backupSummary',
            'healthMetrics'
        ));
    }

    public function usersRoles()
    {
        $roleSummary = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->orderBy('role')
            ->get();

        return view('admin.system.users_roles', compact('roleSummary'));
    }

    public function mfaManagement()
    {
        $users = User::orderBy('name')->get();

        return view('admin.system.mfa', compact('users'));
    }

    public function permissionGroups()
    {
        $groups = PermissionGroup::orderBy('name')->get();

        return view('admin.system.permission_groups.index', compact('groups'));
    }

    public function createPermissionGroup()
    {
        return view('admin.system.permission_groups.create');
    }

    public function storePermissionGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable'],
        ]);

        $permissions = $this->parsePermissionList($validated['permissions'] ?? '');

        $group = PermissionGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $permissions,
        ]);

        AuditLogService::record('Permission Group Created', $group, [], $group->toArray());

        return redirect()->route('portal.admin.system.permission_groups')->with('status', 'Permission group created.');
    }

    public function editPermissionGroup(PermissionGroup $permissionGroup)
    {
        return view('admin.system.permission_groups.edit', compact('permissionGroup'));
    }

    public function updatePermissionGroup(Request $request, PermissionGroup $permissionGroup)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable'],
        ]);

        $permissions = $this->parsePermissionList($validated['permissions'] ?? '');
        $oldValues = $permissionGroup->toArray();

        $permissionGroup->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $permissions,
        ]);

        AuditLogService::record('Permission Group Updated', $permissionGroup, $oldValues, $permissionGroup->toArray());

        return redirect()->route('portal.admin.system.permission_groups')->with('status', 'Permission group updated.');
    }

    public function destroyPermissionGroup(PermissionGroup $permissionGroup)
    {
        $permissionGroup->delete();

        AuditLogService::record('Permission Group Deleted', $permissionGroup, $permissionGroup->toArray(), []);

        return redirect()->route('portal.admin.system.permission_groups')->with('status', 'Permission group deleted.');
    }

    public function notificationRules()
    {
        $settings = $this->loadNotificationRuleSettings();

        return view('admin.system.notification_rules', compact('settings'));
    }

    public function updateNotificationRules(Request $request)
    {
        $validated = $request->validate([
            'notify_on_backup_failure' => ['nullable', 'boolean'],
            'notify_on_failed_jobs' => ['nullable', 'boolean'],
            'notify_on_new_user' => ['nullable', 'boolean'],
            'notify_on_user_status_change' => ['nullable', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            PortalSetting::updateOrCreate(['key' => $key], ['value' => $request->boolean($key) ? '1' : '0']);
        }

        AuditLogService::record('Notification Rules Updated', null, [], $validated);

        return back()->with('status', 'Notification rules updated.');
    }

    public function dataRetention()
    {
        $settings = $this->loadDataRetentionSettings();

        return view('admin.system.data_retention', compact('settings'));
    }

    public function updateDataRetention(Request $request)
    {
        $validated = $request->validate([
            'audit_logs_days' => ['required', 'integer', 'min:0'],
            'backup_records_days' => ['required', 'integer', 'min:0'],
            'restore_records_days' => ['required', 'integer', 'min:0'],
            'disaster_recovery_tests_days' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated as $key => $value) {
            PortalSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        AuditLogService::record('Data Retention Settings Updated', null, [], $validated);

        return back()->with('status', 'Data retention settings updated.');
    }

    public function systemHealth()
    {
        $healthMetrics = $this->systemHealthMetrics();

        return view('admin.system.health', compact('healthMetrics'));
    }

    protected function loadNotificationRuleSettings(): array
    {
        $defaults = [
            'notify_on_backup_failure' => '1',
            'notify_on_failed_jobs' => '1',
            'notify_on_new_user' => '1',
            'notify_on_user_status_change' => '1',
        ];

        return array_replace($defaults, PortalSetting::query()->pluck('value', 'key')->all());
    }

    protected function loadDataRetentionSettings(): array
    {
        $defaults = [
            'audit_logs_days' => '365',
            'backup_records_days' => '730',
            'restore_records_days' => '730',
            'disaster_recovery_tests_days' => '730',
        ];

        return array_replace($defaults, PortalSetting::query()->pluck('value', 'key')->all());
    }

    protected function systemHealthMetrics(): array
    {
        $storageUsage = 0;

        if (Schema::hasTable('users')) {
            foreach (File::allFiles(storage_path()) as $file) {
                $storageUsage += $file->getSize();
            }
        }

        $queueDriver = config('queue.default');
        $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : null;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null;
        $queuedTables = Schema::hasTable('jobs') ? 'database' : 'none';

        return [
            'storage_usage_bytes' => $storageUsage,
            'queue_driver' => $queueDriver,
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'queue_table_mode' => $queuedTables,
            'backup' => $this->backupSummary(),
        ];
    }

    protected function backupSummary(): array
    {
        $lastSuccessful = BackupRecord::successful()->latest('backup_date')->first();
        $failedCount = BackupRecord::failed()->count();
        $pendingCount = BackupRecord::where('status', BackupRecord::STATUS_IN_PROGRESS)->count();

        return [
            'last_successful' => $lastSuccessful?->backup_date?->toDateTimeString() ?? 'Never',
            'failed' => $failedCount,
            'in_progress' => $pendingCount,
            'total' => BackupRecord::count(),
        ];
    }

    protected function parsePermissionList(string $input): array
    {
        return collect(preg_split('/[\r\n,]+/', $input, flags: PREG_SPLIT_NO_EMPTY))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
