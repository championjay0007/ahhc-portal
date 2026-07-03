<?php

namespace App\Http\Controllers;

use App\Models\BackupRecord;
use App\Models\DisasterRecoveryTest;
use App\Models\RestoreRecord;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BackupController extends Controller
{
    public function index()
    {
        $lastSuccessful = BackupRecord::successful()->latest('backup_date')->first();
        $failedBackups = BackupRecord::failed()->count();
        $storageUsage = BackupRecord::sum('size');
        $inProgressBackups = BackupRecord::where('status', BackupRecord::STATUS_IN_PROGRESS)->count();
        $recentBackups = BackupRecord::latest('backup_date')->take(5)->get();
        $recentRestores = RestoreRecord::latest('restore_date')->take(5)->get();
        $recentTests = DisasterRecoveryTest::latest('test_date')->take(5)->get();
        $lastTest = DisasterRecoveryTest::latest('test_date')->first();

        return view('admin.backups.dashboard', compact(
            'lastSuccessful',
            'failedBackups',
            'storageUsage',
            'inProgressBackups',
            'recentBackups',
            'recentRestores',
            'recentTests',
            'lastTest'
        ));
    }

    public function backupHistory(Request $request)
    {
        $backups = BackupRecord::orderBy('backup_date', 'desc')
            ->paginate(15);

        return view('admin.backups.history', compact('backups'));
    }

    public function createBackup()
    {
        return view('admin.backups.create', [
            'backupTypes' => $this->backupTypes(),
            'statuses' => $this->backupStatuses(),
        ]);
    }

    public function storeBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_type' => ['required', 'string', 'in:'.implode(',', $this->backupTypes())],
            'backup_date' => ['required', 'date'],
            'size' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:'.implode(',', $this->backupStatuses())],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $backup = BackupRecord::create(array_merge($validated, [
            'size' => $validated['size'] ?? 0,
        ]));

        AuditLogService::record('backup.recorded', $backup, [], $backup->toArray());

        if ($backup->status === BackupRecord::STATUS_FAILED) {
            $this->alertAdminsOnFailure($backup);
        }

        return redirect()->route('portal.admin.backups.history')
            ->with('success', 'Backup record saved successfully.');
    }

    public function restoreHistory()
    {
        $restores = RestoreRecord::with(['backupRecord', 'initiatedBy'])
            ->orderBy('restore_date', 'desc')
            ->paginate(15);

        return view('admin.backups.restores', compact('restores'));
    }

    public function createRestore()
    {
        return view('admin.backups.restores_create', [
            'backups' => BackupRecord::latest('backup_date')->get(),
            'statuses' => [RestoreRecord::STATUS_SUCCESSFUL, RestoreRecord::STATUS_FAILED],
        ]);
    }

    public function storeRestore(Request $request)
    {
        $validated = $request->validate([
            'backup_record_id' => ['nullable', 'exists:backup_records,id'],
            'restore_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:'.RestoreRecord::STATUS_SUCCESSFUL.','.RestoreRecord::STATUS_FAILED],
            'notes' => ['nullable', 'string'],
        ]);

        $restore = RestoreRecord::create(array_merge($validated, [
            'initiated_by_id' => Auth::id(),
        ]));

        AuditLogService::record('backup.restore_recorded', $restore, [], $restore->toArray());

        return redirect()->route('portal.admin.backups.restores')
            ->with('success', 'Restore log saved successfully.');
    }

    public function disasterRecoveryTests()
    {
        $tests = DisasterRecoveryTest::with('conductedBy')
            ->orderBy('test_date', 'desc')
            ->paginate(15);

        return view('admin.backups.tests', compact('tests'));
    }

    public function createTest()
    {
        return view('admin.backups.tests_create', [
            'statuses' => [
                DisasterRecoveryTest::STATUS_PASSED,
                DisasterRecoveryTest::STATUS_FAILED,
                DisasterRecoveryTest::STATUS_PARTIAL,
            ],
        ]);
    }

    public function storeTest(Request $request)
    {
        $validated = $request->validate([
            'test_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:'.DisasterRecoveryTest::STATUS_PASSED.','.DisasterRecoveryTest::STATUS_FAILED.','.DisasterRecoveryTest::STATUS_PARTIAL],
            'summary' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $test = DisasterRecoveryTest::create(array_merge($validated, [
            'conducted_by_id' => Auth::id(),
        ]));

        AuditLogService::record('backup.disaster_recovery_test_recorded', $test, [], $test->toArray());

        return redirect()->route('portal.admin.backups.tests')
            ->with('success', 'Disaster recovery test recorded successfully.');
    }

    public function complianceReport()
    {
        $totalBackups = BackupRecord::count();
        $successfulBackups = BackupRecord::successful()->count();
        $failureRate = $totalBackups ? round((($totalBackups - $successfulBackups) / $totalBackups) * 100, 1) : 0;
        $lastSuccessfulByType = collect($this->backupTypes())->mapWithKeys(function ($type) {
            $lastBackup = BackupRecord::successful()->where('backup_type', $type)->latest('backup_date')->first();

            return [$type => $lastBackup?->backup_date?->toDateTimeString() ?? 'No successful record'];
        });

        $recentFailures = BackupRecord::failed()->latest('backup_date')->take(10)->get();

        return view('admin.backups.compliance', compact(
            'totalBackups',
            'successfulBackups',
            'failureRate',
            'lastSuccessfulByType',
            'recentFailures'
        ));
    }

    protected function backupTypes(): array
    {
        return [
            BackupRecord::TYPE_DATABASE,
            BackupRecord::TYPE_FILE_STORAGE,
            BackupRecord::TYPE_AUDIT_LOG,
        ];
    }

    protected function backupStatuses(): array
    {
        return [
            BackupRecord::STATUS_SUCCESSFUL,
            BackupRecord::STATUS_FAILED,
            BackupRecord::STATUS_IN_PROGRESS,
        ];
    }

    protected function alertAdminsOnFailure(BackupRecord $backup): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'Backup failure detected',
                'message' => sprintf(
                    'A %s backup failed on %s. Review the backup history for details.',
                    Str::title(str_replace('_', ' ', $backup->backup_type)),
                    $backup->backup_date->format('Y-m-d H:i')
                ),
                'channel' => 'in_app',
                'type' => 'critical',
                'data' => [
                    'url' => route('portal.admin.backups.history'),
                ],
            ]);
        }
    }
}
