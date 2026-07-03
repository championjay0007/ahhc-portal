# Worker Compliance Tracking Module - Implementation Guide

## Quick Start

### 1. Run Migration
```bash
php artisan migrate
```

This creates the `worker_compliance_documents` table with all necessary fields and indexes.

### 2. Initialize Worker Compliance Documents

When a new worker is created, initialize their compliance documents:

```php
use App\Models\Worker;
use App\Services\ComplianceService;

$worker = Worker::find(1);
$complianceService = app(ComplianceService::class);
$complianceService->initializeWorkerCompliance($worker);
```

Or via API:
```
POST /portal/admin/compliance/workers/{worker_id}/initialize
```

### 3. Create/Update a Compliance Document

```php
use App\Models\Worker;
use App\Enums\ComplianceDocumentType;
use App\Services\ComplianceService;

$worker = Worker::find(1);
$complianceService = app(ComplianceService::class);

$document = $complianceService->createOrUpdateDocument(
    worker: $worker,
    documentType: ComplianceDocumentType::POLICE_CHECK,
    issueDate: now()->subYear(),
    expiryDate: now()->addYear(),
    notes: 'Police check renewed'
);
```

Or via API:
```
POST /portal/admin/compliance/documents
{
    "worker_id": 1,
    "document_type": "Police Check",
    "issue_date": "2023-06-05",
    "expiry_date": "2026-06-05",
    "notes": "Valid police check"
}
```

### 4. Upload Document File

```php
// Via API form-data
POST /portal/admin/compliance/documents/{document_id}/upload
file: <binary file>
```

Files are stored in `storage/private/compliance_documents/{worker_id}/`

### 5. Verify Document (Admin Only)

```php
$document = WorkerComplianceDocument::find(1);
$document->markAsVerified(auth()->user());
```

Or via API:
```
POST /portal/admin/compliance/documents/{document_id}/verify
```

### 6. Check Worker Assignability

Before creating an assignment, check compliance:

```php
use App\Services\ComplianceService;

$worker = Worker::find(1);
$complianceService = app(ComplianceService::class);

if (!$complianceService->canWorkerBeAssigned($worker)) {
    $reason = $complianceService->getAssignmentBlockingReason($worker);
    // Show error message
}
```

Or via API:
```
GET /portal/admin/compliance/workers/{worker_id}/assignable
```

Response:
```json
{
    "can_assign": false,
    "blocking_reason": "Worker has compliance issues: Insurance (Expired), NDIS Worker Screening (Expiring Soon)",
    "critical_issues": [...]
}
```

### 7. Get Dashboard Statistics

```php
use App\Services\ComplianceDashboardService;

$dashboardService = app(ComplianceDashboardService::class);
$stats = $dashboardService->getDashboardStats();

// Returns: expiring_soon, expired, missing, workers_with_issues, compliance_score
```

Or via API:
```
GET /portal/admin/compliance/dashboard
```

### 8. Export Compliance Report

```php
use App\Services\ComplianceReportExporter;

$exporter = app(ComplianceReportExporter::class);

// Export all documents
$csv = $exporter->exportAsCSV('all');

// Export specific types
$csv = $exporter->exportExpiringReport(30);
$csv = $exporter->exportMissingReport();
$csv = $exporter->exportWorkerComplianceSummary();
$csv = $exporter->exportRejectedReport();
```

Or via API:
```
GET /portal/admin/compliance/export?type=expiring
GET /portal/admin/compliance/export?type=missing
GET /portal/admin/compliance/export?type=worker_summary
GET /portal/admin/compliance/export?type=rejected
```

### 9. Scheduled Compliance Scanning

The daily compliance scan runs at 3 AM and:
- Updates document statuses (Active → Expiring Soon → Expired)
- Detects status changes
- Logs results

Manually trigger:
```
POST /portal/admin/compliance/scan
```

## Database Queries

### Get documents expiring in 30 days
```php
$documents = WorkerComplianceDocument::where('status', 'Expiring Soon')->get();
```

### Get all expired documents
```php
$documents = WorkerComplianceDocument::where('status', 'Expired')
    ->with('worker')
    ->get();
```

### Get missing documents for a worker
```php
$documents = WorkerComplianceDocument::forWorker($worker_id)
    ->where('status', 'Missing')
    ->get();
```

### Get critical document issues for a worker
```php
$issues = WorkerComplianceDocument::forWorker($worker_id)
    ->critical()
    ->whereNotIn('status', ['Active'])
    ->get();
```

## Notifications

Notifications are sent for:
1. **30-day reminder** - Document expiring within 30 days
2. **14-day reminder** - Document expiring within 14 days
3. **7-day reminder** - Document expiring within 7 days (URGENT)
4. **Expired** - Document has expired (CRITICAL)
5. **Missing** - Worker has missing required documents

Notifications include:
- Email to all admins
- Database notification
- Prevents spam (24-hour check)

## Status Automatic Updates

Documents are automatically updated based on dates:

| Condition | Status |
|-----------|--------|
| No dates | Missing |
| Has dates, past expiry | Expired |
| Has dates, within 30 days | Expiring Soon |
| Has dates, valid | Active |

## Critical Documents

These prevent worker assignment if not Active:
- Police Check
- NDIS Worker Screening
- Insurance

## API Response Examples

### Get All Documents
```
GET /portal/admin/compliance
```

```json
{
    "data": [
        {
            "id": 1,
            "worker_id": 5,
            "document_type": "Police Check",
            "status": "Active",
            "issue_date": "2023-06-05",
            "expiry_date": "2026-06-05",
            "verified_at": "2024-06-05T10:30:00",
            "worker": {...}
        }
    ],
    "pagination": {...}
}
```

### Get Worker Compliance
```
GET /portal/admin/compliance/workers/{worker_id}
```

```json
{
    "worker_id": 5,
    "worker_name": "John Doe",
    "worker_number": "W001",
    "total_documents": 9,
    "active": 6,
    "expiring_soon": 2,
    "expired": 1,
    "missing": 0,
    "can_be_assigned": false,
    "blocking_reason": "Worker has compliance issues: Insurance (Expired)",
    "documents": [...]
}
```

### Dashboard Stats
```
GET /portal/admin/compliance/dashboard
```

```json
{
    "total_workers": 25,
    "expiring_soon": {
        "count": 3,
        "documents": [...]
    },
    "expired": {
        "count": 2,
        "documents": [...]
    },
    "missing": {
        "count": 5,
        "documents": [...]
    },
    "workers_with_issues": {
        "count": 8,
        "workers": [...]
    },
    "compliance_score": 78
}
```

## Security Considerations

- Document files stored in `private` disk (not web accessible)
- Requires `admin` role for verification/rejection
- Database queries use indexes for performance
- Soft deletes not implemented (use hard delete for privacy)

## Performance Tips

1. **Use pagination** when listing documents:
   ```
   GET /portal/admin/compliance?per_page=20&page=1
   ```

2. **Filter by status**:
   ```
   GET /portal/admin/compliance?status=Expiring%20Soon
   ```

3. **Batch operations**:
   - Scan runs in chunks of 100 workers
   - Consider running during off-peak hours

4. **Indexes** are created on:
   - worker_id
   - document_type
   - status
   - expiry_date

## Troubleshooting

### Documents not updating status
- Ensure `php artisan schedule:run` is running
- Check Laravel logs in `storage/logs/`
- Manually trigger: `POST /portal/admin/compliance/scan`

### Notifications not sending
- Configure mail driver in `.env`
- Check queue if using async
- Verify admin users exist with proper role

### Worker can't be assigned
- Check `/portal/admin/compliance/workers/{id}/assignable`
- Review critical document issues
- Upload missing documents or renew expired ones

## Future Enhancements

- PDF generation for compliance reports
- Bulk document upload
- Automated renewal reminders
- Document templates
- Compliance audit trail
- Integration with document management systems
