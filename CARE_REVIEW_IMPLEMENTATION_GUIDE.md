# Monthly Care Review Module - Implementation Guide

## Developer Reference

This guide covers implementation details and usage patterns for the Monthly Care Review module.

## File Structure

```
app/
  ├── Models/
  │   ├── MonthlyCareReview.php      (180+ lines)
  │   ├── CareReviewActivity.php     (40 lines)
  │   └── CareContactLog.php         (55 lines)
  ├── Services/
  │   ├── CareReviewService.php      (350+ lines)
  │   ├── CareReviewDashboardService.php (400+ lines)
  │   └── CareReviewNotificationService.php (150+ lines)
  ├── Notifications/
  │   ├── CareReviewDueReminder.php
  │   └── CareReviewOverdue.php
  ├── Http/Controllers/
  │   └── CareReviewController.php   (250+ lines)
  ├── Jobs/
  │   └── ScanCareReviews.php
  ├── Enums/
  │   ├── ReviewType.php
  │   └── ReviewStatus.php
  └── Console/
      └── Kernel.php (updated)
routes/
  └── web.php (updated with care review routes)
database/
  └── migrations/
      └── 2026_06_05_000002_create_monthly_care_reviews_table.php
tests/Feature/
  └── CareReviewTest.php
```

## Common Usage Patterns

### Creating a Care Review

```php
use App\Services\CareReviewService;
use App\Models\Participant;
use App\Models\User;

$service = app(CareReviewService::class);
$participant = Participant::find($id);
$careManager = User::find($managerId);

$review = $service->createReview(
    $participant,
    $careManager,
    'Standard',  // review type
    '2026-07-05' // optional next review date
);
```

### Completing a Review

```php
$review = MonthlyCareReview::find($reviewId);
$completedReview = $service->completeReview(
    $review,
    auth()->user(),
    'Review completed with positive outcomes',
    '2026-08-05' // optional next review date
);
```

### Adding Concerns and Actions

```php
// Add a concern
$service->addConcerns($review, 'Participant reported increased mobility concerns');

// Add required actions
$service->addActionsRequired($review, 'Schedule physiotherapy assessment');

// Multiple additions accumulate with timestamps
```

### Querying Reviews

```php
use App\Models\MonthlyCareReview;
use App\Enums\ReviewStatus;

// Get reviews due in next 7 days
$dueReviews = MonthlyCareReview::due()->limit(10)->get();

// Get reviews for specific participant
$reviews = MonthlyCareReview::forParticipant($participantId)->get();

// Get all overdue reviews
$overdueReviews = MonthlyCareReview::overdue()->get();

// Get completed reviews in date range
$completed = MonthlyCareReview::dateBetween('2026-01-01', '2026-01-31')->get();

// Custom query
$reviews = MonthlyCareReview::where('status', ReviewStatus::COMPLETED->value)
    ->where('care_manager_id', $managerId)
    ->orderByDesc('completed_at')
    ->paginate(20);
```

### Dashboard Statistics

```php
use App\Services\CareReviewDashboardService;

$dashboard = app(CareReviewDashboardService::class);

// Get all statistics
$stats = $dashboard->getDashboardStats();
// Returns: due, completed, overdue, compliance_rate, etc.

// Get specific data
$due = $dashboard->getReviewsDue();
$compliance = $dashboard->getComplianceRate();
$workload = $dashboard->getCareManagerWorkload();

// Generate reports
$outstanding = $dashboard->getOutstandingReviewsReport();
$monthly = $dashboard->getMonthlyCompletionReport('2026-06');

// Export functionality
$csv = $dashboard->exportMonthlyReportAsCSV('2026-06');
```

### Working with Contact Logs

```php
// Add a contact log
$log = $service->addContactLog(
    $review,
    $participant,
    auth()->user(),
    'In-person',           // contact_type
    'Home visit',          // contact_method
    'Discussed progress',  // notes
    'Positive response',   // outcomes
    true,                  // follow_up_required
    '2026-06-20'          // follow_up_date
);
```

### Activity Logging

```php
// Activity is automatically logged on major operations
// But you can log custom activities

$service->logActivity(
    $review,
    auth()->id(),
    'custom_action',
    'Description of what happened'
);

// View activity log
$activities = $review->activities()
    ->orderByDesc('created_at')
    ->get();
```

### Sending Notifications

```php
use App\Services\CareReviewNotificationService;

$notificationService = app(CareReviewNotificationService::class);

// Send specific notifications
$notificationService->send7DayReminder($review);
$notificationService->sendDueTodayReminder($review);
$notificationService->sendOverdueNotification($review);

// Check and send reminders for a single review
$notificationService->checkAndSendReminders($review);

// Bulk scan and send (typically run via scheduled job)
$results = $notificationService->scanAndSendReminders();
// Returns: ['sent_7_day' => 5, 'sent_today' => 2, 'sent_overdue' => 1, 'total_processed' => 50]
```

## API Usage Examples

### List Reviews with Filtering

```http
GET /portal/admin/care-reviews?participant_id=5&status=Due&per_page=20
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "participant_id": 5,
      "care_manager_id": 2,
      "review_type": "Standard",
      "status": "Due",
      "next_review_date": "2026-06-20",
      "created_at": "2026-05-20T10:30:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Get Dashboard Statistics

```http
GET /portal/admin/care-reviews/dashboard/stats
```

Response:
```json
{
  "reviews_due": {
    "count": 15,
    "reviews": [...]
  },
  "reviews_completed": {
    "count": 42,
    "recent": [...]
  },
  "reviews_overdue": {
    "count": 3,
    "reviews": [...]
  },
  "compliance_rate": {
    "rate": 93.33,
    "completed": 42,
    "total": 45
  },
  "care_manager_workload": [
    {
      "care_manager": "John Smith",
      "total_participants": 12,
      "reviews_due": 3,
      "reviews_overdue": 1,
      "workload_priority": 5
    }
  ]
}
```

### Complete a Review

```http
POST /portal/admin/care-reviews/1/complete
Content-Type: application/json

{
  "completion_notes": "Review completed successfully. Participant progressing well.",
  "next_review_date": "2026-07-20"
}
```

### Export Outstanding Reviews

```http
GET /portal/admin/care-reviews/export/outstanding
```

Returns CSV file:
```csv
Participant,Care Manager,Status,Due Date,Days
John Doe,Jane Smith,Due,2026-06-15,3
Alice Johnson,Bob Wilson,Overdue,2026-06-10,8
```

## Middleware & Authorization

The care review endpoints are protected with `middleware(['role:admin'])`. 

To allow other roles access:
1. Update route definitions in `routes/web.php`
2. Add middleware to controller methods
3. Implement policy checks if needed

```php
// In routes/web.php
Route::middleware(['role:admin,care_manager'])->group(function () {
    Route::resource('care-reviews', CareReviewController::class);
});
```

## Testing

Run tests with:
```bash
php artisan test tests/Feature/CareReviewTest.php
```

Key test scenarios covered:
- Review creation and default scheduling
- Status transitions (Due → Completed → Next Review)
- Activity logging and audit trail
- Concern and action tracking
- Dashboard statistics accuracy
- CSV export format
- API endpoints
- Permission checks

## Configuration

The module uses standard Laravel configuration:
- Database: Uses default connection
- Queue: Can be configured in `config/queue.php`
- Notifications: Uses configured mail driver
- Scheduling: Daily at 8 AM (configurable in `app/Console/Kernel.php`)

To change scheduled time:
```php
// In app/Console/Kernel.php
$schedule->job(new ScanCareReviews)
    ->dailyAt('06:00')  // Change time here
    ->withoutOverlapping()
    ->onOneServer();
```

## Troubleshooting

### Notifications Not Sending

1. Check queue is running: `php artisan queue:listen`
2. Verify notification channels in `config/mail.php`
3. Check notification timestamps (24-hour deduplication window)
4. Review logs in `storage/logs/`

### Status Updates Not Triggering

1. Verify cron job is running: `php artisan schedule:list`
2. Check job execution: `php artisan schedule:run --verbose`
3. Review `storage/logs/` for scan errors
4. Ensure database has proper indexes

### Dashboard Statistics Wrong

1. Run manual scan: `php artisan tinker` then `App\Services\CareReviewService::scanAndUpdateReviewStatuses()`
2. Check for timezone mismatches in `config/app.php`
3. Verify participant relationships are loaded correctly
4. Check for deleted records in care manager assignments

## Performance Considerations

- Reviews are paginated (15 per page default)
- Dashboard queries use eager loading (`with()`) to prevent N+1
- Activity logs are indexed by review_id for quick lookups
- Notification scanning uses chunking for large datasets
- Status updates use batch processing for efficiency

For large datasets:
```php
// Instead of loading all at once
MonthlyCareReview::all();

// Use pagination
MonthlyCareReview::paginate(50);

// Or chunking for processing
MonthlyCareReview::chunk(100, function ($reviews) {
    // Process $reviews
});
```

## Integration with Existing Modules

The module integrates with:
- **User Model**: Via care_manager_id and completed_by_id
- **Participant Model**: Via participant_id, includes relationships
- **Notification System**: Uses Laravel's notification system
- **Activity Logging**: Compatible with audit trail patterns
- **Dashboard**: Can be integrated with admin dashboard

## Next Steps

1. Create Blade views for dashboard
2. Add JavaScript for real-time data loading
3. Implement advanced search/filter UI
4. Add bulk operations (bulk complete, bulk assign)
5. Create participant-facing view of reviews
6. Add review templates by type
