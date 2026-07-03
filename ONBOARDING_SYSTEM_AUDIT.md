# Participant Onboarding System - Comprehensive Audit

**Generated:** June 18, 2026  
**System:** AHHC Portal - Laravel Application  
**Scope:** Complete onboarding flow from participant registration through activation

---

## 1. DATA MODELS - Complete Overview

### 1.1 Participant Model
**File:** [app/Models/Participant.php](app/Models/Participant.php)

#### Status Constants Defined:
```php
STATUS_ACTIVE = 'active'
STATUS_INACTIVE = 'inactive'
STATUS_ONBOARDING = 'onboarding'
STATUS_PENDING_ADMIN_REVIEW = 'pending_admin_review'
STATUS_AHHC_REVIEW = 'ahhc_review'
STATUS_ELIGIBILITY_ASSESSMENT = 'eligibility_assessment'
STATUS_SUITABILITY_ASSESSMENT = 'suitability_assessment'
STATUS_CLOSED = 'closed'
```

#### Fillable Fields:
- `user_id` - Foreign key to User
- `participant_number` - Unique identifier (auto-generated as P-{id})
- `first_name`, `last_name`, `preferred_name`
- `date_of_birth` - Date cast
- `status` - Current participant status
- `onboarding_token` - UUID token for onboarding link (unique)
- `onboarding_expires_at` - Timestamp (14 days from creation)
- `care_plan_start_date`, `care_plan_end_date`
- `primary_language`
- `address`, `city`, `state`, `postcode`
- `phone`, `email`
- `medical_alerts`, `notes`
- `consent_to_share` - Boolean flag
- `budget_limit_cents`, `current_budget_used_cents`
- `assigned_support_person_id` - FK to SupportPerson
- `created_by_id`, `updated_by_id` - Audit tracking

#### Key Relationships:
- `user()` - BelongsTo User (portal login account)
- `supportPerson()` - BelongsTo SupportPerson
- `participantStatusHistories()` - HasMany (audit trail)

#### Boot Methods:
- Auto-records status history on creation

---

### 1.2 OnboardingProgress Model
**File:** [app/Models/OnboardingProgress.php](app/Models/OnboardingProgress.php)

#### Purpose:
Tracks participant's progress through 8-step onboarding wizard

#### Fields:
- `participant_id` - FK (unique - one per participant)
- `current_step` - Integer 1-8 (default 1)
- `completed_steps` - JSON array of completed step numbers
- `draft_data` - JSON object storing form data for save-as-draft feature
- `status` - Enum: 'in_progress', 'draft', 'complete'
- `completed_at` - Timestamp when participant finished all 8 steps

#### Key Methods:
- `markStepComplete(int $step)` - Adds step to completed array
- `stepProgress()` - Returns completion %: `(current_step - 1) / 8 * 100`
- `completionPercentage()` - Returns `count(completed_steps) / 8 * 100`
- `isComplete()` - Boolean check if `status === 'complete'`

#### Status Flow:
1. **in_progress** - Default, participant is actively filling form
2. **draft** - User clicked "Save Draft" button
3. **complete** - User submitted final step (all 8 steps), awaits admin review

---

### 1.3 Document Model
**File:** [app/Models/Document.php](app/Models/Document.php)

#### Purpose:
Polymorphic model for all document types (participants, workers, compliance, etc.)

#### Fillable Fields:
- `owner_type`, `owner_id` - MorphTo (Participant, Worker, etc.)
- `document_type` - Category/type of document
- `description` - Optional text description
- `title` - Display name
- `storage_disk` - 'local', 's3', etc.
- `path` - File path on disk
- `mime_type` - File MIME type
- `size_bytes` - File size
- `uploaded_by_id` - FK to User who uploaded
- `status` - 'active', 'uploaded', 'signed', 'expired'
- `onboarding_required` - Boolean flag (for wizard documents)
- `expires_at` - Optional expiration date
- `is_sensitive` - Boolean flag
- `metadata` - JSON object

#### Constants:
```php
ALLOWED_FILE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'csv']
MAX_FILE_SIZE_KB = 10240
PARTICIPANT_DOCUMENT_CATEGORIES = [
    'Care Plan', 'Support Plan', 'Referral Documents', 
    'Authority Documents', 'Funding Documents', 'Identification', 'Other'
]
MANDATORY_PARTICIPANT_DOCUMENT_CATEGORIES = [
    'Care Plan', 'Support Plan', 'Identification'
]
```

#### Key Methods:
- `fileValidationRules()` - Returns validation rules for uploads
- `isMandatoryParticipantDocument()` - Checks if document is required
- `owner()` - MorphTo relationship

#### Related Models:
- `DocumentSignature` - Records electronic signatures
- `ParticipantDocumentSignature` - Participant-specific signature records
- `DocumentVersion` - Tracks document revisions
- `ParticipantDocument` - Alias/wrapper model

---

### 1.4 DocumentSignature Model
**File:** [app/Models/DocumentSignature.php](app/Models/DocumentSignature.php) *(referenced)*

#### Purpose:
Records digital signature events for documents

#### Fields:
- `document_id` - FK
- `signed_by_type`, `signed_by_id` - MorphTo (User)
- `signature_method` - 'electronic'
- `signed_at` - Timestamp
- `ip_address`, `user_agent` - Audit trail
- `signature_hash` - SHA256 hash for verification
- `signature_path` - Path to signature image PNG
- `signature_disk` - Storage disk
- `signed_document_path` - Path to signed PDF
- `signed_document_disk` - Storage disk
- `certificate_path` - Path to certificate PDF
- `certificate_disk` - Storage disk

---

### 1.5 ParticipantStatusHistory Model
**File:** [app/Models/ParticipantStatusHistory.php](app/Models/ParticipantStatusHistory.php) *(referenced)*

#### Purpose:
Complete audit trail of all status changes

#### Key Fields:
- `participant_id` - FK
- `from_status` - Previous status
- `to_status` - New status
- `changed_by_id` - FK to User who made change
- `reason` - Text explanation

---

### 1.6 User Model (Auth Integration)
**File:** [app/Models/User.php](app/Models/User.php) *(referenced)*

#### Fields Related to Onboarding:
- `role` - 'participant', 'worker', 'admin', 'system_admin'
- `status` - 'active', 'inactive'
- `mfa_enabled` - Boolean
- `mfa_secret` - Encrypted MFA code
- `last_login_at` - Timestamp
- `password_changed_at` - Timestamp

#### Relationship to Onboarding:
- When `status='onboarding'` created in Participant model, User status is set to 'inactive'
- User password is set to random 32-char string
- Password changed only when participant completes onboarding step 1

---

### 1.7 SupportPerson Model
**File:** [app/Models/SupportPerson.php](app/Models/SupportPerson.php) *(referenced)*

#### Purpose:
Stores emergency support/guardian contact information

#### Fields:
- `user_id` - FK
- `first_name`, `last_name`
- `relationship` - Family/professional relationship
- `phone`, `email`
- `address`, `city`, `state`, `postcode`

#### Used In Onboarding:
- Step 5 of wizard captures support person details
- Created/updated via `SupportPerson::updateOrCreate()` in submit method

---

## 2. DATABASE STRUCTURE - Table Analysis

### 2.1 Participants Table
**Migration:** [2026_06_15_000001_add_participant_onboarding_fields.php](database/migrations/2026_06_15_000001_add_participant_onboarding_fields.php)

#### Key Columns Added for Onboarding:
```
onboarding_token VARCHAR UNIQUE NULLABLE
onboarding_expires_at TIMESTAMP NULLABLE
```

#### Additional Columns (from Participant model):
```
id BIGINT PRIMARY KEY
user_id BIGINT FK
participant_number VARCHAR UNIQUE
first_name VARCHAR(100)
last_name VARCHAR(100)
preferred_name VARCHAR(100)
date_of_birth DATE
status VARCHAR (8 options)
care_plan_start_date DATE
care_plan_end_date DATE
primary_language VARCHAR(100)
address VARCHAR(255)
city VARCHAR(100)
state VARCHAR(100)
postcode VARCHAR(50)
phone VARCHAR(50)
email VARCHAR(255)
medical_alerts TEXT
notes TEXT
consent_to_share BOOLEAN
budget_limit_cents INT
current_budget_used_cents INT
assigned_support_person_id BIGINT FK
created_by_id BIGINT FK
updated_by_id BIGINT FK
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

### 2.2 OnboardingProgress Table
**Migration:** [2026_06_16_000001_create_onboarding_progress_table.php](database/migrations/2026_06_16_000001_create_onboarding_progress_table.php)

```
id BIGINT PRIMARY KEY
participant_id BIGINT FK UNIQUE CONSTRAINED
current_step TINYINT UNSIGNED DEFAULT 1
completed_steps JSON
draft_data JSON
status VARCHAR DEFAULT 'in_progress'
completed_at TIMESTAMP
created_at TIMESTAMP
updated_at TIMESTAMP
```

**Cascade Delete:** When participant deleted, onboarding progress auto-deleted

---

### 2.3 Documents Table
**Migration:** [2026_06_17_000001_add_onboarding_fields_to_documents.php](database/migrations/2026_06_17_000001_add_onboarding_fields_to_documents.php)

#### New Columns:
```
onboarding_required BOOLEAN DEFAULT false
description TEXT
```

#### All Document Columns:
```
id BIGINT PRIMARY KEY
owner_type VARCHAR (MorphTo)
owner_id BIGINT
document_type VARCHAR
description TEXT
title VARCHAR
storage_disk VARCHAR
path VARCHAR
mime_type VARCHAR
size_bytes INT
uploaded_by_id BIGINT FK
status VARCHAR
onboarding_required BOOLEAN
expires_at DATE
is_sensitive BOOLEAN
metadata JSON
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

### 2.4 ParticipantDocumentSignatures Table
**Migration:** [2026_06_17_000002_create_participant_document_signatures_table.php](database/migrations/2026_06_17_000002_create_participant_document_signatures_table.php)

```
id BIGINT PRIMARY KEY
participant_id BIGINT FK
document_id BIGINT FK
signed_at TIMESTAMP
signature_data LONGTEXT (base64 encoded image)
ip_address VARCHAR
user_agent TEXT
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

### 2.5 DocumentSignatures Table (Global)
**Related Migration:** [document signing implementation]

```
id BIGINT PRIMARY KEY
document_id BIGINT FK
signed_by_type VARCHAR (MorphTo)
signed_by_id BIGINT
signature_method VARCHAR
signed_at TIMESTAMP
ip_address VARCHAR
user_agent TEXT
signature_hash VARCHAR(64)
signature_path VARCHAR
signature_disk VARCHAR
signed_document_path VARCHAR
signed_document_disk VARCHAR
certificate_path VARCHAR
certificate_disk VARCHAR
created_at TIMESTAMP
```

---

## 3. ROUTES - Complete URL Mapping

### 3.1 Public/Guest Routes (No Authentication Required)

**File:** [routes/web.php](routes/web.php)

```php
# Onboarding entry point (token-based, 14-day expiry)
GET    /portal/onboarding/{token}                              → show()
POST   /portal/onboarding/{token}                              → submit()

# Document preview/signing during onboarding
GET    /portal/onboarding/{token}/document/{document}          → showOnboardingDocument()
GET    /portal/onboarding/{token}/document/{document}/preview  → previewOnboardingDocument()
POST   /portal/onboarding/{token}/document/{document}/sign     → signOnboardingDocument()
```

**Route Names:**
- `portal.onboarding.show`
- `portal.onboarding.submit`
- `portal.onboarding.document.show`
- `portal.onboarding.document.preview`
- `portal.onboarding.document.sign`

---

### 3.2 Authenticated Participant Routes

```php
GET    /portal/onboarding/status                               → status()
```

**Route Name:** `portal.onboarding.status`

**Middleware:** `auth` (logged in participant)

---

### 3.3 Admin Routes - Participant Management

```php
# Participant CRUD
GET    /portal/admin/participants                              → participants()
GET    /portal/admin/participants/create                       → createParticipant()
POST   /portal/admin/participants                              → storeParticipant()
GET    /portal/admin/participants/{participant}                → showParticipant()
GET    /portal/admin/participants/{participant}/edit           → editParticipant()
PUT    /portal/admin/participants/{participant}                → updateParticipant()
DELETE /portal/admin/participants/{participant}                → destroyParticipant()

# Participant Status Actions
POST   /portal/admin/participants/{participant}/approve         → approveParticipant()
POST   /portal/admin/participants/{participant}/reject          → rejectParticipant()
POST   /portal/admin/participants/{participant}/request-changes → requestParticipantChanges()

# Onboarding Invitation
POST   /portal/admin/participants/{participant}/resend-onboarding → resendParticipantOnboardingInvitation()
```

**Route Prefix:** `portal.admin.participants`

**Middleware:** `auth`, `mfa`, `role:admin|system_admin`

---

### 3.4 Document Management Routes (Admin)

```php
GET    /portal/admin/documents                                  → documents()
GET    /portal/admin/documents/create                           → createDocument()
POST   /portal/admin/documents                                  → storeDocument()
GET    /portal/admin/documents/{document}                       → showDocument()
POST   /portal/admin/documents/{document}/toggle-onboarding     → toggleDocumentOnboarding()
POST   /portal/admin/documents/{document}/versions              → uploadDocumentVersion()
GET    /portal/admin/documents/{document}/preview               → previewDocument()
GET    /portal/admin/documents/{document}/download              → downloadDocument()
```

---

## 4. CONTROLLERS - Method Details

### 4.1 ParticipantOnboardingController
**File:** [app/Http/Controllers/ParticipantOnboardingController.php](app/Http/Controllers/ParticipantOnboardingController.php)

#### Public Methods:

**1. `show(string $token)`**
- **Purpose:** Display onboarding wizard form
- **Auth:** None (token-based)
- **Validation:**
  - Token must exist in `onboarding_token` field
  - Participant status must be 'onboarding'
  - Token expiry must be in future
- **Returns:** View `auth.onboarding` with:
  - `$participant`, `$token`, `$progress`, `$requireMfa`, `$draftData`, `$supportPerson`
- **Side Effects:** Creates OnboardingProgress record if not exists

**2. `submit(Request $request, string $token)`**
- **Purpose:** Process wizard form submission (any step)
- **Parameters:**
  - `current_step` - Form parameter (1-8)
  - `save_draft` - Boolean flag
  - Form fields based on current step
- **Validation:** 
  - Calls `validationRulesForCurrentStep()` for draft saves
  - Full validation on final submission
- **Flow:**
  - Saves draft if `save_draft` flag set
  - If step < 8: update progress and redirect
  - If step == 8: complete entire onboarding
- **Final Step (8) Actions:**
  - Marks progress as 'complete'
  - Sets participant status to `PENDING_ADMIN_REVIEW`
  - Clears onboarding token/expiry
  - Validates all required documents uploaded
  - Validates all required agreements signed
  - Creates signed agreement PDFs and certificates
  - Creates/updates SupportPerson if provided
  - Saves uploaded documents
  - Sends notifications to all admins
  - Redirects to `portal.onboarding.status`

**3. `showOnboardingDocument(string $token, Document $document)`**
- **Purpose:** Display admin-assigned document for signing during wizard
- **Validation:**
  - Document must have `onboarding_required = true`
  - Document status must be 'active'
- **Returns:** View `auth.onboarding.onboarding_document`

**4. `previewOnboardingDocument(string $token, Document $document)`**
- **Purpose:** Serve PDF/image file for preview
- **Returns:** File response with inline disposition

**5. `signOnboardingDocument(Request $request, string $token, Document $document)`**
- **Purpose:** Process participant's digital signature on document
- **Validation:**
  - `confirm_signature` must be accepted
  - `signature_image` must be provided (base64 PNG data)
  - Document must have `onboarding_required = true`
- **Actions:**
  - Creates DocumentSignature record
  - Creates ParticipantDocumentSignature record
  - Sets document status to 'signed'
  - Notifies admins
  - Records audit log
- **Returns:** Redirect to onboarding wizard with success message

**6. `status()`**
- **Purpose:** Show onboarding status page for authenticated participant
- **Auth:** `auth` middleware
- **Logic:**
  - If participant status is 'active', redirect to dashboard
  - Otherwise, show status page with appropriate message:
    - `ONBOARDING` → "Complete your onboarding using the invitation link"
    - `PENDING_ADMIN_REVIEW` → "Your onboarding has been submitted and is awaiting AHHC review"
    - `AHHC_REVIEW` → "Your application is currently being reviewed by AHHC"
    - `ELIGIBILITY_ASSESSMENT` → "Your eligibility is being assessed"
  - Show resume link if onboarding token still valid

#### Protected Methods:

**7. `validationRulesForCurrentStep(int $currentStep): array`**
- Returns Laravel validation rules array for given step
- **Step 1:** Password (required, 8+ chars, confirmed)
- **Step 2:** MFA acknowledgment
- **Step 3:** Preferred name, phone
- **Step 4:** Emergency contact (name, relationship, phone, email)
- **Step 5:** Support person (optional, all contact fields)
- **Step 6:** Document upload (optional, multiple file types)
- **Step 7:** Agreements (self-mgmt, privacy, responsibilities, terms) + full name + signature
- **Step 8:** Combination of all above

**8. `validationRulesForStep(int $currentStep): array`**
- Wrapper that calls above, but forces full validation on step 8

**9. `collectDraftData(Request $request): array`**
- Collects relevant form fields from request into draft_data JSON

**10. `isMfaRequiredForOnboarding(): bool`**
- Checks PortalSetting or system config

---

### 4.2 AdminController - Participant Onboarding Methods
**File:** [app/Http/Controllers/AdminController.php](app/Http/Controllers/AdminController.php)

#### Public Methods:

**1. `participants(Request $request)`**
- **Purpose:** List all participants with filters
- **Parameters:**
  - `search` - Search by number, name, email, phone
  - `status` - Filter by participant status
- **Returns:** Paginated view `admin.participants` (20 per page)
- **Data Passed:**
  - `$participants` (with user, assignments, workers)
  - `$workers` (list for assignment)

**2. `createParticipant()`**
- **Purpose:** Display form to create new participant
- **Returns:** View `admin.participants.create`
- **Data:** `$supportPeople` (list for assignment)

**3. `storeParticipant(Request $request)`**
- **Purpose:** Create new participant from admin form
- **Validation:**
  - Email unique in users table
  - Participant number unique (or auto-generate)
  - Password required unless status='onboarding'
- **Logic:**
  - Creates User with role='participant'
  - If onboarding status:
    - User status set to 'inactive'
    - Random 32-char password generated
    - Participant status='onboarding'
    - Generates UUID token
    - Expiry set to +14 days
    - Sends `ParticipantOnboardingInvitation` email
  - Sends notification to participant
- **Returns:** Redirect to participants list with success message

**4. `showParticipant(Participant $participant)`**
- **Purpose:** Display detailed participant profile and progress
- **Data Loaded:**
  - Participant with all relationships
  - OnboardingProgress (if exists)
  - Documents (uploaded and required agreements)
  - Required vs signed agreements (to show gaps)
  - Budget metrics and transactions
  - Risk scores
  - Status history
  - Audit logs
- **Returns:** View `admin.participant`

**5. `editParticipant(Participant $participant)`**
- **Purpose:** Display participant edit form
- **Returns:** View `admin.participants.edit`

**6. `updateParticipant(Request $request, Participant $participant)`**
- **Purpose:** Update participant details
- **Validation:**
  - Similar to storeParticipant but with unique constraints on existing ID
- **Logic:**
  - If changing status from onboarding to active:
    - Calls `validateParticipantActivationRequirements()` 
    - Checks: onboarding complete, documents uploaded, agreements signed
    - Returns errors if validation fails
  - Updates User and Participant
  - If status is still 'onboarding':
    - Refreshes onboarding token if expired
    - Resets expiry to +14 days
    - Resends invitation email
- **Returns:** Redirect with success or errors

**7. `destroyParticipant(Participant $participant)`**
- **Purpose:** Delete participant (cascades to OnboardingProgress)
- **Returns:** Redirect to list with confirmation

**8. `resendParticipantOnboardingInvitation(Participant $participant)`**
- **Purpose:** Re-send onboarding email
- **Validation:** Participant status must be 'onboarding'
- **Action:** Sends `ParticipantOnboardingInvitation` email
- **Returns:** Redirect with status message

**9. `approveParticipant(Participant $participant)`**
- **Purpose:** Approve completed onboarding and activate account
- **Validation:** Calls `validateParticipantActivationRequirements()`
- **Actions:**
  - Sets status to ACTIVE
  - Sets user status to 'active'
  - Records status history
  - Sends approval notification
- **Returns:** Redirect with success

**10. `rejectParticipant(Request $request, Participant $participant)`**
- **Purpose:** Reject onboarding and request changes
- **Actions:**
  - Sets status to ONBOARDING (restart)
  - Regenerates token
  - Resets expiry
  - Sends rejection message with reasons
  - Records audit
- **Returns:** Redirect with confirmation

**11. `validateParticipantActivationRequirements(Participant $participant): array`**
- **Returns:** Array of error messages if validation fails
- **Checks:**
  - OnboardingProgress exists and status='complete'
  - At least one mandatory document category uploaded (Care Plan, Support Plan, OR Identification)
  - All required agreements signed:
    - Self-Management Agreement
    - Privacy Consent
    - Responsibilities Agreement
    - Terms & Conditions
  - All admin-assigned onboarding documents (where `onboarding_required=true`) have signatures

---

### 4.3 AuthController - Related Methods
**File:** [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php)

**1. `register(Request $request)`**
- Creates participant or worker account
- Sets status to 'active'
- Calls `createRoleProfile()` to generate Participant/Worker records

**2. `login(Request $request)`**
- Validates credentials
- Checks user status (inactive users can't login unless in specific onboarding states)
- Handles MFA flow if enabled
- Records login audit

**3. `showAdminCreate()`**
- Admin form to create new user/participant

**4. `createUser(Request $request)`**
- Admin creates new participant/worker/admin user

---

## 5. VIEWS - Onboarding Interface

### 5.1 Main Onboarding Wizard View
**File:** [resources/views/auth/onboarding.blade.php](resources/views/auth/onboarding.blade.php)

- **Purpose:** Multi-step wizard container
- **Features:**
  - Step progress indicator
  - Step navigation sidebar
  - Form with current step shown
  - Save draft functionality
  - Responsive design
- **Included Steps (8 total):**
  - `steps/account.blade.php` - Password setup
  - `steps/mfa.blade.php` - MFA acknowledgment
  - `steps/profile.blade.php` - Personal details
  - `steps/emergency.blade.php` - Emergency contact
  - `steps/support.blade.php` - Support person
  - `steps/documents.blade.php` - Upload documents
  - `steps/agreements.blade.php` - Sign agreements
  - `steps/review.blade.php` - Final review

### 5.2 Onboarding Status Page
**File:** [resources/views/auth/onboarding-status.blade.php](resources/views/auth/onboarding-status.blade.php)

- **Purpose:** Shows participant their current onboarding status
- **Logic:**
  - If status=onboarding and token valid: show resume button
  - If status=pending_admin_review: show waiting message
  - If status=active: redirect to dashboard (via controller)
  - Show different messages per status

### 5.3 Document Signing View
**File:** [resources/views/auth/onboarding/onboarding_document.blade.php](resources/views/auth/onboarding/onboarding_document.blade.php)

- Shows document preview
- Digital signature pad
- Confirmation checkbox
- Submit button

---

## 6. MIDDLEWARE - Access Control

**File:** [app/Http/Middleware/](app/Http/Middleware/)

### 6.1 EnsureOnboardingComplete
**File:** [app/Http/Middleware/EnsureOnboardingComplete.php](app/Http/Middleware/EnsureOnboardingComplete.php)

- **Purpose:** Force participants in onboarding status to complete wizard
- **Logic:**
  - Checks: `Auth::check()` && `user->role === 'participant'` && `participant->status === ONBOARDING`
  - If in progress and token valid: redirect to `portal.onboarding.show`
  - Otherwise: logout and redirect to login with error
- **Whitelist Routes:**
  - `portal.onboarding.*`
  - `portal.login`
  - `portal.register`
  - `portal.mfa.*`

### 6.2 EnsureRole
**File:** [app/Http/Middleware/EnsureRole.php](app/Http/Middleware/EnsureRole.php)

- **Purpose:** Role-based access control
- **Usage:** `Route::middleware('role:admin|system_admin')`
- **Aborts 403** if user role not in allowed list

### 6.3 EnsureTwoFactorEnabled
**File:** [app/Http/Middleware/EnsureTwoFactorEnabled.php](app/Http/Middleware/EnsureTwoFactorEnabled.php)

- **Purpose:** Enforce MFA setup in onboarding

### 6.4 EnforceAssessmentWorkflow
**File:** [app/Http/Middleware/EnforceAssessmentWorkflow.php](app/Http/Middleware/EnforceAssessmentWorkflow.php)

- **Purpose:** Prevent bypass of assessment workflow stages
- **Used for:** Assessment-related routes

---

## 7. STATUS ENUMS & CONSTANTS

### 7.1 Participant Status Enum
**File:** [app/Models/Participant.php](app/Models/Participant.php) (constants, not dedicated enum)

```
STATUS_ACTIVE = 'active'
STATUS_INACTIVE = 'inactive'
STATUS_ONBOARDING = 'onboarding'
STATUS_PENDING_ADMIN_REVIEW = 'pending_admin_review'
STATUS_AHHC_REVIEW = 'ahhc_review'
STATUS_ELIGIBILITY_ASSESSMENT = 'eligibility_assessment'
STATUS_SUITABILITY_ASSESSMENT = 'suitability_assessment'
STATUS_CLOSED = 'closed'
```

**Status Flow Diagram:**
```
ONBOARDING (initiated by admin)
    ↓ (participant completes all 8 steps)
PENDING_ADMIN_REVIEW (awaiting admin approval)
    ├→ REJECTED (admin requests changes, loops back to ONBOARDING)
    ├→ AHHC_REVIEW (admin approves, sent to AHHC for compliance review)
    │   └→ ELIGIBILITY_ASSESSMENT
    │       └→ SUITABILITY_ASSESSMENT
    │           └→ ACTIVE (fully approved)
    └→ ACTIVE (fast-tracked approval)

ACTIVE ←→ INACTIVE (status management)
ACTIVE → CLOSED (end of care)
```

### 7.2 Document Status Values
```
'active' - Ready for use (in onboarding forms)
'uploaded' - Uploaded by participant, pending review
'signed' - Digitally signed
'expired' - Past expiry date
```

### 7.3 OnboardingProgress Status Values
```
'in_progress' - Actively filling form
'draft' - Form saved as draft
'complete' - All 8 steps submitted, awaiting admin review
```

### 7.4 Review Status Enum
**File:** [app/Enums/ReviewStatus.php](app/Enums/ReviewStatus.php)

```
PENDING
IN_PROGRESS
COMPLETED
CANCELLED
```

### 7.5 Document Categories
```
PARTICIPANT_DOCUMENT_CATEGORIES:
- Care Plan
- Support Plan
- Referral Documents
- Authority Documents
- Funding Documents
- Identification
- Other

MANDATORY_PARTICIPANT_DOCUMENT_CATEGORIES:
- Care Plan
- Support Plan
- Identification
```

---

## 8. SERVICES - Business Logic

### 8.1 OnboardingAgreementService
**File:** [app/Services/OnboardingAgreementService.php](app/Services/OnboardingAgreementService.php)

#### Required Agreements:
```php
[
    'agreement_self_management' => 'Self-Management Agreement',
    'agreement_privacy' => 'Privacy Consent',
    'agreement_responsibilities' => 'Responsibilities Agreement',
    'agreement_terms' => 'Terms & Conditions',
]
```

#### Methods:

**`requiredAgreements(): array`**
- Static method returning all 4 required agreements

**`createSignedAgreement(Participant $participant, string $agreementKey, string $fullName, string $signatureImage, string $ipAddress, string $userAgent): DocumentSignature`**
- **Purpose:** Generate signed agreement PDF with certificate
- **Actions:**
  - Generates unique filename with timestamp
  - Renders `pdfs.onboarding-agreement` view to PDF
  - Encodes signature image and stores as PNG
  - Renders `pdfs.onboarding-signature-certificate` view to PDF
  - Creates Document record with status='signed'
  - Creates DocumentSignature record
  - Stores all 3 files (PDF, signature PNG, certificate PDF)
- **Returns:** DocumentSignature instance

#### PDF Templates:
- `resources/views/pdfs/onboarding-agreement.blade.php`
- `resources/views/pdfs/onboarding-signature-certificate.blade.php`

---

### 8.2 AuditLogService
- Records all significant actions: logins, onboarding submissions, document signs, status changes
- Used extensively in onboarding flow

### 8.3 NotificationService
- Sends notifications to participants and admins
- Used for onboarding invitations, completion notifications

### 8.4 NotificationCenterService
- Alternative notification method for system notifications

---

## 9. EMAILS - Participant Communication

### 9.1 ParticipantOnboardingInvitation
**File:** [app/Mail/ParticipantOnboardingInvitation.php](app/Mail/ParticipantOnboardingInvitation.php)

**Template:** [resources/views/mail/participant-onboarding-invitation.blade.php](resources/views/mail/participant-onboarding-invitation.blade.php)

- **Triggered:** When new participant created with status='onboarding' or existing participant reverted to onboarding
- **Contents:**
  - Welcome message
  - Onboarding link with token
  - 14-day expiry warning
  - Instructions for 8-step process
  - Support contact information

---

## 10. VALIDATION RULES

### 10.1 Onboarding Submission Validation

**Step 1 (Account):**
```
password: required|string|min:8|confirmed
```

**Step 3 (Profile):**
```
preferred_name: required|string|max:100
phone: required|string|max:50
```

**Step 4 (Emergency):**
```
emergency_contact_name: required|string|max:100
emergency_contact_relationship: required|string|max:100
emergency_contact_phone: required|string|max:50
emergency_contact_email: nullable|email|max:150
```

**Step 5 (Support Person):**
```
support_first_name: nullable|string|max:100
support_last_name: nullable|string|max:100
support_email: nullable|email|max:150
support_phone: nullable|string|max:50
support_relationship: nullable|string|max:100
support_address: nullable|string|max:255
support_city: nullable|string|max:100
support_state: nullable|string|max:100
support_postcode: nullable|string|max:50
```

**Step 6 (Documents):**
```
document_title: nullable|string|max:150
document_description: nullable|string|max:255
document_file: nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv|max:10240
```

**Step 7 (Agreements):**
```
agreement_self_management: accepted
agreement_privacy: accepted
agreement_responsibilities: accepted
agreement_terms: accepted
agreement_full_name: required|string|max:150
signature_image: required|string (base64 PNG)
```

### 10.2 Participant Creation (Admin)
- Email unique in users table
- Phone optional, max 50 chars
- Password required if not onboarding status, min 8 chars, confirmed
- Participant number optional (auto-generates as P-{id})
- First/last name required, max 100 chars
- Status must be in Participant::statusOptions()
- Date fields (DOB, care plan dates) optional, proper date format
- Budget fields optional, numeric, min 0

### 10.3 Document Upload
- File: required, file, allowed extensions, max 10240 KB
- All document uploads in onboarding require one of 3 mandatory categories

### 10.4 Activation Requirements Check
- OnboardingProgress::status must = 'complete'
- At least one document from mandatory categories (Care Plan, Support Plan, or Identification)
- All 4 required agreements must be signed
- All admin-assigned onboarding documents must be signed

---

## 11. KEY DATA FLOWS & WORKFLOWS

### 11.1 Complete Onboarding Workflow

```
1. INITIATION (Admin)
   └─ Admin creates participant with status='onboarding'
   │  └─ User record created with role='participant', status='inactive'
   │  └─ Unique UUID token generated
   │  └─ Token expires +14 days
   │  └─ ParticipantOnboardingInvitation email sent

2. PARTICIPANT ACCESS (Token-based)
   └─ Participant receives email with onboarding link: /portal/onboarding/{token}
   └─ Token validated (must exist, not expired, participant status='onboarding')
   └─ OnboardingProgress created if not exists
   └─ Wizard view rendered with 8 steps

3. STEP PROGRESSION (Steps 1-7)
   └─ Step N form submitted
   └─ Validation rules for Step N applied
   └─ If save_draft=true:
   │  └─ Form data saved to OnboardingProgress.draft_data
   │  └─ Redirect to wizard (can resume later with same link)
   └─ Else:
      └─ Progress updated (current_step, completed_steps, status='in_progress')
      └─ Data persisted in User and Participant models
      └─ Support person created/updated if provided

4. DOCUMENT HANDLING (Steps 5-6)
   └─ Admin-assigned documents (onboarding_required=true):
   │  └─ Presented for participant signature
   │  └─ Signature stored in ParticipantDocumentSignature
   │  └─ Document status set to 'signed'
   └─ Participant uploads personal documents:
      └─ Stored with owner_type=Participant
      └─ Document status='uploaded'
      └─ Admins notified of upload

5. AGREEMENT SIGNING (Step 7)
   └─ Participant confirms acceptance of 4 required agreements
   └─ Provides full name and digital signature (canvas image)
   └─ OnboardingAgreementService.createSignedAgreement() called for each:
   │  └─ PDF generated from pdfs.onboarding-agreement template
   │  └─ Signature PNG saved
   │  └─ Certificate PDF generated
   │  └─ Document record created with status='signed'
   │  └─ DocumentSignature record with signature hash created
   └─ Audit logged for each agreement

6. FINAL SUBMISSION (Step 8)
   └─ All validations re-run:
   │  └─ At least one mandatory document uploaded
   │  └─ All admin-assigned onboarding docs signed
   │  └─ All 4 required agreements signed
   └─ OnboardingProgress.status set to 'complete'
   └─ OnboardingProgress.completed_at set to now()
   └─ Participant.status set to PENDING_ADMIN_REVIEW
   └─ Participant.onboarding_token and expiry cleared
   └─ All admins notified via NotificationCenterService
   └─ Participant redirected to /portal/onboarding/status

7. ADMIN REVIEW
   └─ Admin reviews participant in /portal/admin/participants/{id}
   │  └─ Sees:
   │     ├─ Onboarding progress (8/8 steps)
   │     ├─ Uploaded documents with categories
   │     ├─ Signed agreements
   │     ├─ Support person info
   │     └─ All audit trail
   └─ Admin calls validateParticipantActivationRequirements():
   │  └─ If errors: shows missing items
   │  └─ If valid: approve button enabled
   └─ Admin clicks approve:
      └─ POST /portal/admin/participants/{id}/approve
      └─ Status changed to ACTIVE
      └─ User.status changed to 'active'
      └─ StatusHistory recorded
      └─ Activation notification sent to participant

8. REJECTION (If needed)
   └─ Admin calls rejectParticipant():
   │  └─ Status reset to ONBOARDING
   │  └─ Token regenerated
   │  └─ Expiry reset to +14 days
   │  └─ Rejection email sent with specific reasons
   │  └─ Participant can restart wizard with new link
```

### 11.2 Token Expiry & Access Control

```
VALID TOKEN:
- Exists in DB at participants.onboarding_token
- participants.onboarding_expires_at is in future
- participants.status = 'onboarding'
- Links: /portal/onboarding/{token}, /portal/onboarding/{token}/document/{doc}, etc.

EXPIRED/INVALID TOKEN:
- Token not found, OR
- Participant status ≠ 'onboarding', OR
- onboarding_expires_at <= now()
- Result: 404 or redirect to login with error message

AUTHENTICATED PARTICIPANT:
- Can access /portal/onboarding/status without token
- Shows current status and resume option if token valid
- If status=ACTIVE, redirects to dashboard
```

---

## 12. IDENTIFIED ISSUES & GAPS

### 12.1 Critical Issues

**1. Status='inactive' During Onboarding**
- **Issue:** When participant created with status='onboarding', User.status set to 'inactive'
- **Problem:** Inactive users cannot login (checked in AuthController::login)
- **Solution:** Participant cannot access portal until final approval, must use token-based wizard
- **Risk:** If token expires, participant has no way to access their account without admin intervention

**2. Token Expiry No Refresh**
- **Issue:** 14-day token has no refresh mechanism
- **Problem:** If participant saves draft on day 13, resumes on day 15, token is expired
- **Gap:** No way for participant to request extension or new token
- **Solution Needed:** Auto-extend token when active wizard session detected

**3. No Partial Submission Recovery**
- **Issue:** If participant starts step 7 (agreements), saves draft, then returns, previous progress is lost
- **Problem:** Draft data may not capture all form state across all steps
- **Gap:** No mechanism to restore UI state from draft_data JSON

### 12.2 Moderate Issues

**4. MFA Setup Happens After Onboarding**
- **Issue:** MFA setup deferred until after onboarding complete
- **Problem:** Account activation delayed if MFA required
- **Solution:** Could offer optional MFA setup during onboarding (step 2)

**5. No Batch Email on Multiple Onboarding Requests**
- **Issue:** Each admin action (create, update, resend) sends separate emails
- **Problem:** Participant inbox may get flooded
- **Solution:** Track invitations to avoid duplicates within 24 hours

**6. SupportPerson Optional but Encouraged**
- **Issue:** Support person (step 5) is entirely optional
- **Problem:** Many participants should have emergency contact but it's skipped
- **Solution:** Make at least one contact field (phone or email) required

**7. Document Category Flexibility**
- **Issue:** Only 1 of 3 mandatory categories needed, but no guidance on which
- **Problem:** Participant might upload wrong category by mistake
- **Solution:** Clearer UI labeling of mandatory categories

### 12.3 Minor Issues

**8. No Document Expiry Enforcement**
- **Issue:** Uploaded documents can expire but system doesn't flag them
- **Solution:** Add expiry warnings in admin participant view

**9. Audit Trail Incomplete for Drafts**
- **Issue:** Draft saves not logged in audit trail
- **Solution:** Consider audit logging for draft submissions

**10. No Participant Communication Templates**
- **Issue:** Rejection reasons passed as free text, no templates
- **Solution:** Create rejection reason templates

---

## 13. SECURITY CONSIDERATIONS

### 13.1 Authentication & Authorization

✅ **Implemented:**
- Token validation on all onboarding routes
- Expiry checking
- Participant status verification
- Role-based access (role:admin|system_admin)
- MFA support in flow

⚠️ **Considerations:**
- Token is UUID in URL (can be brute forced if not rate-limited)
- Email contains full onboarding link (can be intercepted)
- No rate limiting on token validation attempts

### 13.2 Data Protection

✅ **Implemented:**
- Signature hashes (SHA256)
- IP address and user agent logging
- Audit trail for all status changes

⚠️ **Considerations:**
- Signature image stored as base64 in database (space inefficient, should be file-based)
- Draft data stored as JSON includes all participant info (not encrypted)

### 13.3 File Uploads

✅ **Implemented:**
- Allowed extensions whitelist
- File size limit (10MB)
- MIME type checking

⚠️ **Considerations:**
- No virus scanning mentioned
- No filename sanitization documented
- Storage path predictable from participant ID

---

## 14. PERFORMANCE CONSIDERATIONS

### 14.1 Database Queries

**Potential N+1 Issues:**
- `showParticipant()` loads: user, assignments, workers, careNotes, incidents, invoices, etc. (many with relationships)
- Consider adding eager loading

**Missing Indexes:**
- `onboarding_token` (has unique constraint, so indexed)
- `participants.status` (filtered frequently)
- `onboarding_progress.participant_id` (unique constraint, so indexed)

### 14.2 File Storage

- All files stored on `local` disk (filesystem)
- Consider S3 for scalability
- Certificate generation (PDF) on every agreement could be slow

### 14.3 Caching Opportunities

- PortalSetting for MFA requirement (loaded per request)
- Document categories and mandatory list

---

## 15. RELATED SYSTEMS

### 15.1 Assessment Workflow Integration
- After activation, participant may enter eligibility/suitability assessment phases
- Managed by AssessmentController, not onboarding

### 15.2 Compliance & Document Management
- Worker compliance documents are separate system
- Uses same Document model but different categories

### 15.3 Budget & Financial System
- Participant budget setup happens post-onboarding
- Related to BudgetService

### 15.4 Notification System
- Integrated via NotificationService and NotificationCenterService
- Supports multiple channels (email, in-app, SMS)

---

## 16. RECOMMENDATIONS

### Immediate (Critical)

1. **Add Token Auto-Refresh**
   - Extend expiry to +14 days whenever wizard is accessed
   - Prevents mid-flow expiration

2. **Improve MFA Error Handling**
   - Show clearer message if MFA setup required during onboarding
   - Provide skip option with warning

3. **Document Upload Validation**
   - Add virus scanning using ClamAV or similar
   - Add document preview in admin review

### Short-Term (High Priority)

4. **Enhance Draft Recovery**
   - Add restore UI state from draft_data
   - Show last saved timestamp

5. **Support Person Redesign**
   - Make at least phone OR email required
   - Show validation message

6. **Batch Invitations**
   - Track recent invitations
   - Skip email if sent < 24 hours ago
   - Add resend attempt counter

### Medium-Term (Improvements)

7. **Rate Limiting**
   - Add rate limit to token validation endpoints
   - Prevent brute force attacks

8. **Email Encryption**
   - Encrypt onboarding links in emails
   - Use click-through tracking instead

9. **Signature Storage**
   - Move signatures to file-based storage instead of base64 in DB
   - Implement signature image versioning

10. **Comprehensive Testing**
    - Add tests for all 8 wizard steps
    - Test draft save/resume
    - Test document upload/signing
    - Test admin approval workflow

---

## 17. SUMMARY TABLE

| Component | Status | Implementation | Quality |
|-----------|--------|-----------------|---------|
| Data Models | ✅ Complete | 7 core models (Participant, OnboardingProgress, Document, etc.) | Solid |
| Database | ✅ Complete | 5 migrations, proper relationships | Well-indexed |
| Routes | ✅ Complete | 14 public + admin routes | RESTful |
| Controllers | ✅ Complete | 3 controllers, 15+ methods | Well-organized |
| Middleware | ✅ Complete | 4 middleware (role, onboarding, MFA) | Functional |
| Views | ⚠️ Partial | 8-step wizard exists, base views present | Needs responsive testing |
| Services | ✅ Complete | OnboardingAgreementService, AuditLog, etc. | Functional |
| Email | ✅ Complete | ParticipantOnboardingInvitation template | Basic but adequate |
| Validation | ✅ Complete | All 8 steps validated | Comprehensive |
| Security | ✅ Good | Token validation, role-based access, audit trails | Could improve encryption |
| Performance | ⚠️ Needs Review | Potential N+1 queries, file storage on local disk | Monitor on scaling |
| Testing | ❌ Unknown | No visible tests in audit scope | Needs coverage |

---

## 18. FILE REFERENCE INDEX

### Models
- [app/Models/Participant.php](app/Models/Participant.php)
- [app/Models/OnboardingProgress.php](app/Models/OnboardingProgress.php)
- [app/Models/Document.php](app/Models/Document.php)
- [app/Models/DocumentSignature.php](app/Models/DocumentSignature.php)
- [app/Models/User.php](app/Models/User.php)
- [app/Models/SupportPerson.php](app/Models/SupportPerson.php)

### Controllers
- [app/Http/Controllers/ParticipantOnboardingController.php](app/Http/Controllers/ParticipantOnboardingController.php)
- [app/Http/Controllers/AdminController.php](app/Http/Controllers/AdminController.php)
- [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php)

### Services
- [app/Services/OnboardingAgreementService.php](app/Services/OnboardingAgreementService.php)
- [app/Services/AuditLogService.php](app/Services/AuditLogService.php)
- [app/Services/NotificationService.php](app/Services/NotificationService.php)

### Middleware
- [app/Http/Middleware/EnsureOnboardingComplete.php](app/Http/Middleware/EnsureOnboardingComplete.php)
- [app/Http/Middleware/EnsureRole.php](app/Http/Middleware/EnsureRole.php)

### Routes
- [routes/web.php](routes/web.php)

### Migrations
- [database/migrations/2026_06_15_000001_add_participant_onboarding_fields.php](database/migrations/2026_06_15_000001_add_participant_onboarding_fields.php)
- [database/migrations/2026_06_16_000001_create_onboarding_progress_table.php](database/migrations/2026_06_16_000001_create_onboarding_progress_table.php)
- [database/migrations/2026_06_17_000001_add_onboarding_fields_to_documents.php](database/migrations/2026_06_17_000001_add_onboarding_fields_to_documents.php)

### Views
- [resources/views/auth/onboarding.blade.php](resources/views/auth/onboarding.blade.php)
- [resources/views/auth/onboarding-status.blade.php](resources/views/auth/onboarding-status.blade.php)
- [resources/views/auth/onboarding/onboarding_document.blade.php](resources/views/auth/onboarding/onboarding_document.blade.php)

### Enums
- [app/Enums/ComplianceDocumentType.php](app/Enums/ComplianceDocumentType.php)

---

**End of Audit Report**
