# Worker Onboarding System - Implementation Guide

## Overview

The AHHC Portal now includes a comprehensive 6-stage worker onboarding workflow that guides workers from initial invitation through full assignment to participants. The system is built on Laravel best practices with token-based access, stage-based progression, and admin management capabilities.

## System Architecture

### Database Schema

**Workers Table Additions:**
- `onboarding_stage` (1-6): Current stage of onboarding
- `onboarding_token`: Unique token for invitation link
- `onboarding_expires_at`: Expiration date for invitation
- Various timestamp columns for each stage completion
- Foreign key references to admin users who reviewed/approved at each stage

**New Tables:**
- `worker_declarations`: Stores signed declarations (privacy, incident reporting, etc.)
- `worker_service_approvals`: Tracks approved service categories for each worker

### Models

**Worker Model:**
- Methods: `getCurrentStage()`, `moveToNextStage()`, `isOnboardingComplete()`, `canAccessParticipantData()`
- Relationships: `declarations()`, `serviceApprovals()`, `invitedBy()`, `stage2Reviewer()`, etc.
- Scopes for querying workers by stage/status

**WorkerDeclaration Model:**
- Tracks each required declaration and signing status
- Methods: `isSigned()`, `isDeclined()`
- Supports signature file storage

**WorkerServiceApproval Model:**
- Tracks approved services for worker
- Methods: `isApproved()`, `isExpired()`, `isActive()`
- Supports time-limited approvals

### Enums

**WorkerOnboardingStage:**
- STAGE_1_INVITED (1): Account created, MFA setup
- STAGE_2_COMPLIANCE (2): Upload compliance documents
- STAGE_3_REVIEW (3): AHHC reviews documents
- STAGE_4_DECLARATIONS (4): Sign declarations
- STAGE_5_SERVICES (5): Service approval
- STAGE_6_ASSIGNED (6): Assigned to participant

**WorkerDeclarationType:**
- PRIVACY: Privacy and confidentiality agreement
- INCIDENT_REPORTING: Incident reporting requirement
- CARE_NOTES: Care notes submission requirement
- NO_COMMENCEMENT: No commencement before approval
- CODE_OF_CONDUCT: Code of conduct adherence
- THIRD_PARTY: Third-party service agreements

## Workflow

### Stage 1: Invited
**Duration:** Until account creation and MFA enrollment
**Admin Action:** Invite worker via email with token-based link
**Worker Action:** Create account, set strong password, enroll in 2FA
**Output:** User account created, worker advances to Stage 2

### Stage 2: Upload Compliance
**Duration:** Flexible, worker-initiated
**Requirements:**
- ABN (Australian Business Number)
- Police Check
- NDIS Worker Screening
- Insurance (Professional Indemnity)
- Qualifications and Certifications
- Training Certificates
- CPR/First Aid Certification
- Marketplace Agreement (optional)

**Worker Action:** Upload required documents
**Admin Action:** Review all documents for completeness and validity
**Output:** Compliance documents stored, worker advances to Stage 3

### Stage 3: Document Review
**Duration:** 3-5 business days typical
**Admin Action:** Review submitted compliance documents for:
- Document authenticity
- Expiry dates
- Coverage adequacy
- Completeness
**Output:** Documents approved, worker advances to Stage 4

### Stage 4: Sign Declarations
**Duration:** Until all declarations signed
**Requirements:** Sign 6 mandatory declarations:
1. Privacy Agreement
2. Incident Reporting Commitment
3. Care Notes Requirement
4. No Commencement Before Approval
5. Code of Conduct
6. Third-Party Service Agreements

**Worker Action:** Review and digitally sign all declarations
**Output:** Declarations stored with signatures, worker advances to Stage 5

### Stage 5: Service Approval
**Duration:** Until services defined
**Admin Action:** Define approved service categories for worker:
- Personal Care
- Cleaning/Domestic Support
- Medication Support
- Shopping/Errands
- etc.

**Features:**
- Optional time-limited approvals (start/end dates)
- Service descriptions stored
- Unlimited service categories per worker

**Output:** Service categories approved, worker advances to Stage 6

### Stage 6: Assigned to Participant
**Duration:** Ongoing
**Admin Action:** Create ParticipantAssignment linking worker to participant(s)
**Worker Access:** Full access to:
- Assigned participant name, address, phone
- Assigned shifts
- Care notes submission
- Incident/risk form reporting

**Restrictions:** Workers CANNOT see:
- Full funding details
- Clinical documents
- Management records
- Other participants' information
- Unassigned participant data

## Key Features

### Access Control
- Workers can only access assigned participants' limited information
- Stage 6+ required for participant data visibility
- ParticipantPolicy enforces authorization

### Token-Based Invitations
- Each invitation has unique 32-character token
- Tokens expire after 30 days (configurable)
- Invitations can be resent if expired
- Prevents unauthorized access

### Document Management
- Compliance documents stored in private storage
- Support for multiple file types (PDF, DOC, images)
- 10MB file size limit per document
- Automatic virus scanning (if configured)

### Email Notifications
- Invitation email with personalized link
- Stage completion notifications (if configured)
- Expiration reminders (if configured)

### Admin Dashboard
- View all workers with current stage
- Progress timeline visualization
- Filter by stage/status
- One-click advancement or rejection
- Notes for each stage transition
- Bulk actions (future enhancement)

### Worker Dashboard
- Completion percentage indicator
- Clear instructions for current stage
- Timeline showing completed/pending stages
- FAQ and support links
- Estimated timeframes

## Routes

### Worker Onboarding (Public, Token-Protected)
```
GET    /worker/onboarding/{token}              - Show current stage
POST   /worker/onboarding/{token}/stage1       - Submit account details
POST   /worker/onboarding/{token}/stage2       - Upload compliance docs
POST   /worker/onboarding/{token}/stage4       - Sign declarations
```

### Admin Management (Protected)
```
GET    /portal/admin/worker-onboarding/        - List all workers
GET    /portal/admin/worker-onboarding/{worker} - Show worker details
POST   /portal/admin/worker-onboarding/invite   - Invite new worker
POST   /portal/admin/worker-onboarding/{worker}/stage2/approve  - Approve Stage 2
POST   /portal/admin/worker-onboarding/{worker}/stage2/reject   - Reject Stage 2
POST   /portal/admin/worker-onboarding/{worker}/stage3/approve  - Approve Stage 3
POST   /portal/admin/worker-onboarding/{worker}/stage4/approve  - Approve Stage 4
POST   /portal/admin/worker-onboarding/{worker}/stage5/services - Add service
POST   /portal/admin/worker-onboarding/{worker}/stage5/approve  - Approve Stage 5
POST   /portal/admin/worker-onboarding/{worker}/reject          - Reject onboarding
```

## Views

### Worker Views
- `portal/worker/onboarding/stage1_create.blade.php` - Account creation form
- `portal/worker/onboarding/stage2_compliance.blade.php` - Document upload
- `portal/worker/onboarding/stage3_review.blade.php` - Review status
- `portal/worker/onboarding/stage4_declarations.blade.php` - Declaration signing
- `portal/worker/onboarding/stage5_services.blade.php` - Services view
- `portal/worker/onboarding/stage6_complete.blade.php` - Completion page

### Admin Views
- `admin/worker_onboarding/index.blade.php` - Worker list with invite modal
- `admin/worker_onboarding/show.blade.php` - Detailed worker management

## Usage Examples

### Inviting a Worker (Admin)
1. Navigate to `/portal/admin/worker-onboarding/`
2. Click "+ Invite Worker" button
3. Fill in worker details (name, email, phone, role)
4. Click "Send Invitation"
5. Email sent with onboarding link

### Worker Onboarding Flow
1. Receive invitation email
2. Click link with token → Stage 1
3. Create account and enable 2FA
4. Upload compliance documents
5. Wait for admin review
6. Sign declarations
7. Admin defines service categories
8. Admin assigns to participant
9. Full access granted

### Advancing a Worker (Admin)
1. Go to worker detail page
2. Review current stage details
3. Add notes if needed (optional)
4. Click "Approve & Move to Next Stage"
5. Worker notified of progression
6. Documents/declarations locked for that stage

## Configuration

### Invitation Expiration
Edit migration or .env:
```php
$worker->onboarding_expires_at = now()->addDays(30); // in controller
```

### Compliance Document Requirements
Edit `WorkerOnboardingController::getStage2ComplianceRequirements()`

### Declaration Text
Edit `WorkerDeclarationType::defaultText()` enum method

### Service Categories
User-defined per worker, entered in admin interface

## Security Considerations

1. **Token Security:**
   - 32-character random tokens
   - Unique per worker
   - Expiration enforcement
   - Not reusable after completion

2. **Access Control:**
   - Token validation for each stage
   - MFA required for worker login
   - ParticipantPolicy enforces participant visibility
   - Workers cannot modify their own stage

3. **Data Protection:**
   - Compliance documents stored privately
   - Signatures stored securely
   - Declarations immutable once signed
   - Audit trail available via timestamps

4. **Email Security:**
   - Invitations queued for reliable delivery
   - No sensitive data in email bodies
   - Links expire with tokens

## Testing

### Manual Testing Checklist
- [ ] Invite worker - email received
- [ ] Follow invitation link - Stage 1 form loads
- [ ] Create account - redirects to login
- [ ] Login with 2FA - Stage 2 appears
- [ ] Upload compliance docs - files stored
- [ ] Admin approves Stage 2 - worker sees Stage 3
- [ ] Admin approves Stage 3 - declarations appear
- [ ] Sign all declarations - checkbox requirement
- [ ] Submit declarations - Stage 4 completed
- [ ] Admin adds service categories - services visible
- [ ] Admin approves Stage 5 - Stage 6 appears
- [ ] Worker sees assigned participants - limited data
- [ ] Admin can see worker in worker list - correct stage shown

### Automated Testing (Future)
```php
// Test token generation
// Test stage progression
// Test access control
// Test declaration signing
// Test service approval
// Test email sending
```

## Troubleshooting

### Common Issues

**Q: Invitation email not received**
- Check email configuration in .env
- Verify queue/mail driver settings
- Check spam folder
- Resend invitation from admin panel

**Q: Token expired**
- Admin can resend invitation from worker detail page
- New token generated with fresh expiration

**Q: Worker cannot upload documents**
- Verify file size < 10MB
- Check file type is allowed
- Verify storage permissions
- Check disk space

**Q: Declarations not appearing**
- Admin must approve Stage 3 first
- Declarations auto-created on Stage 3 approval
- Check worker_declarations table

**Q: Worker cannot see participants**
- Verify worker is in Stage 6
- Verify ParticipantAssignment exists
- Check assignment status is 'active'
- Verify ParticipantPolicy allows access

## Future Enhancements

1. **Bulk Operations:**
   - Invite multiple workers at once
   - Batch approve Stage 2 documents
   - Mass assign workers to participants

2. **Advanced Features:**
   - Document expiry tracking
   - Automatic renewal reminders
   - Service category expiration notices
   - Performance metrics/analytics

3. **Integration:**
   - Connect with payroll system
   - Integrate with background check providers
   - Link with insurance verification
   - Marketplace integration

4. **Improvements:**
   - Mobile-responsive forms
   - Signature pad for drawing signatures
   - Document preview/annotation
   - Progress notifications via SMS

## Support

For issues or questions:
1. Check this documentation
2. Review WORKER_ONBOARDING_WORKFLOW.txt for business rules
3. Check application logs: `storage/logs/`
4. Contact system administrator
