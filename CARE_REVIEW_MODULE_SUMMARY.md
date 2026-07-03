# Monthly Care Review Module - Implementation Summary

## Module Overview

The Monthly Care Review module is a comprehensive care management system designed for NDIS portal operations. It enables care managers to track and document monthly reviews for participants, ensuring compliance with care requirements.

## Key Features

### 1. Review Management
- Create monthly care reviews for participants
- Track review types: Standard, Interim, Annual, Initial, Reassessment, Transition
- Monitor review statuses: Due, Completed, Overdue, In Progress, Cancelled
- Schedule next reviews with configurable dates (default 30 days)
- Add concerns and required actions to reviews
- Complete reviews with notes and completion tracking

### 2. Audit Trail & Activity Logging
- Automatic logging of all review activities (creation, updates, completion)
- Track who performed actions and when
- Record IP addresses and user agents for security
- Support for viewing activity history

### 3. Contact Tracking
- Log participant contact history related to reviews
- Track contact types: Phone, In-person, Email, Virtual
- Record contact methods, notes, and outcomes
- Schedule follow-up actions with dates
- Link contacts to specific reviews (optional)

### 4. Notifications
- 7-day reminder before review due date
- Due-today notifications for urgent action
- Overdue notifications with escalation alerts
- Sent to care managers, admins, and support coordinators
- Database notifications for portal visibility
- Email notifications with action links

### 5. Dashboard & Reporting
- Real-time statistics (due, completed, overdue counts)
- Compliance rate calculations
- Care manager workload analysis
- Monthly completion reports
- Outstanding reviews reports
- CSV export functionality

### 6. Scheduled Operations
- Daily automatic status updates (Due → Overdue)
- Scheduled at 8 AM for review scanning
- Automatic reminder notifications
- Non-overlapping execution on single server

## Database Schema

### Tables

#### monthly_care_reviews
Primary table for care review tracking with the following columns:
- participant_id, care_manager_id - Foreign keys to participants and users
- review_type - Enum value from ReviewType
- review_date, next_review_date, due_date - Date fields
- status - Enum value from ReviewStatus
- notes, concerns, actions_required - JSON/text fields
- completed_at, completed_by_id - Completion metadata
- Timestamp columns: created_at, updated_at
- Reminder tracking: due_date_reminder_sent_at, today_reminder_sent_at, overdue_reminder_sent_at

#### care_review_activities
Audit trail table with:
- monthly_care_review_id - Links to parent review
- user_id - Which user performed the action
- activity_type - Enum: created, updated, completed, viewed
- description, changes (JSON) - What changed and how
- ip_address, user_agent - Request metadata

#### care_contact_logs
Contact history table with:
- participant_id - The participant contacted
- care_manager_id - Who made the contact
- monthly_care_review_id - Nullable link to specific review
- contact_datetime - When contact occurred
- contact_type - Enum: Phone, In-person, Email, Virtual
- contact_method, notes, outcomes - Contact details
- follow_up_required, follow_up_date - Follow-up tracking

## Service Layer

### CareReviewService
Core business logic (350+ lines, 30+ methods):
- Review CRUD operations
- Status management and transition logic
- Activity logging with detailed change tracking
- Contact log creation
- Statistical queries (due soon, due today, overdue)
- Automatic status scanning and updates
- Dashboard statistics generation

### CareReviewDashboardService
Analytics and reporting (400+ lines, 15+ methods):
- Comprehensive dashboard statistics
- Compliance rate calculations
- Care manager workload analysis
- Monthly and outstanding reviews reports
- CSV export functionality
- Participant review history

### CareReviewNotificationService
Notification coordination (150+ lines):
- 7-day reminder notifications
- Due-today reminder notifications
- Overdue alert notifications
- Recipient management (care managers, admins, support coordinators)
- Notification deduplication (24-hour window)
- Bulk scanning and sending

## API Endpoints

### Review Management
- `GET /portal/admin/care-reviews` - List reviews with filtering
- `POST /portal/admin/care-reviews` - Create new review
- `GET /portal/admin/care-reviews/{id}` - View review details
- `PUT /portal/admin/care-reviews/{id}` - Update review information
- `POST /portal/admin/care-reviews/{id}/complete` - Mark review completed

### Dashboard & Statistics
- `GET /portal/admin/care-reviews/dashboard/stats` - Get all statistics
- `GET /portal/admin/care-reviews/dashboard/due` - Get due reviews
- `GET /portal/admin/care-reviews/dashboard/completed` - Get completed reviews
- `GET /portal/admin/care-reviews/dashboard/overdue` - Get overdue reviews
- `GET /portal/admin/care-reviews/dashboard/workload` - Care manager workload

### Reports & Exports
- `GET /portal/admin/care-reviews/report/outstanding` - Outstanding reviews
- `GET /portal/admin/care-reviews/report/monthly` - Monthly completion report
- `GET /portal/admin/care-reviews/export/outstanding` - Export outstanding as CSV
- `GET /portal/admin/care-reviews/export/monthly` - Export monthly as CSV

### References
- `GET /portal/admin/care-reviews/review-types` - Available review types
- `GET /portal/admin/care-reviews/statuses` - Available statuses
- `GET /portal/admin/care-reviews/{id}/activity` - Activity log for review
- `GET /portal/admin/care-reviews/participant/{id}/history` - Participant history

## Enum Definitions

### ReviewType (6 types)
- STANDARD: Regular scheduled review
- INTERIM: Between standard reviews
- ANNUAL: Yearly comprehensive review
- INITIAL: First review for new participants
- REASSESSMENT: Full participant reassessment
- TRANSITION: Review during participant transitions

### ReviewStatus (5 statuses)
- DUE: Review is currently due
- COMPLETED: Review has been completed
- OVERDUE: Review is overdue
- IN_PROGRESS: Review is being worked on
- CANCELLED: Review has been cancelled

## Scheduled Jobs

### ScanCareReviews (Daily at 8 AM)
Runs automatically each morning to:
1. Update review statuses (Due → Overdue based on date)
2. Send reminder notifications (7-day, due-today, overdue)
3. Log execution results

## Testing

Comprehensive test suite (18+ test cases) in `tests/Feature/CareReviewTest.php`:
- Review creation with default scheduling
- Review completion workflow
- Status transitions and date calculations
- Concern and action tracking
- Activity logging verification
- Query scopes and filters
- API endpoints validation
- CSV export functionality

## Development Notes

### Key Architectural Decisions
1. **Separated concerns** into 3 normalized tables (reviews, activities, contacts)
2. **Enum-based** status and type management for type safety
3. **Service layer pattern** with clear separation of concerns
4. **Queueable notifications** for async delivery
5. **Activity logging** at service level (not middleware) for fine-grained tracking
6. **Notification timestamps** to prevent duplicate sending

### Integration Points
- Uses Eloquent ORM for all database operations
- Integrated with Laravel's notification system
- Leverages task scheduling via Kernel console
- Compatible with existing User and Participant models
- Follows established portal patterns from Phase 1 (Worker Compliance)

## Future Enhancements

Potential areas for expansion:
- SMS notifications for urgent alerts
- Care review templates by review type
- Automated review scheduling based on participant plan
- Integration with participant support goals
- Advanced filtering and search on dashboard
- Role-based notification preferences
- Review escalation workflows
- Care review document generation
