<?php

use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\AdminApplicationsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminOnboardingController;
use App\Http\Controllers\AdminWorkerOnboardingController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetReportController;
use App\Http\Controllers\BudgetTransactionController;
use App\Http\Controllers\CareNoteController;
use App\Http\Controllers\CareReviewController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\Dashboards\AdminDashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MfaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ParticipantOnboardingController;
use App\Http\Controllers\ParticipantPortalController;
use App\Http\Controllers\PreApprovalController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SupportCenterController;
use App\Http\Controllers\SupportConversationController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SystemAdminController;
use App\Http\Controllers\WorkerNominationController;
use App\Http\Controllers\WorkerOnboardingController;
use App\Http\Controllers\WorkerPortalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', [PublicWebsiteController::class, 'index'])->name('public.home');
Route::post('/enquiries', [PublicWebsiteController::class, 'storeEnquiry'])->name('public.enquiries.store');

Route::get('/portal', [AuthController::class, 'showLogin'])->name('portal.login');
Route::get('/portal/login', function () {
    return redirect()->route('portal.login');
});
Route::get('/login', function () {
    return redirect()->route('portal.login');
})->name('login');
Route::get('/offline', function () {
    return response()->file(public_path('offline.html'));
})->name('offline');

Route::middleware('guest')->group(function () {
    Route::post('/portal/login', [AuthController::class, 'login'])->name('portal.login.submit');
    Route::get('/password/reset', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');

    Route::get('/portal/password/reset', [AuthController::class, 'showForgotPasswordForm'])->name('portal.password.request');
    Route::post('/portal/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('portal.password.email');
    Route::get('/portal/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('portal.password.reset');
    Route::post('/portal/password/reset', [AuthController::class, 'resetPassword'])->name('portal.password.update');
    Route::get('/portal/register', function () {
        return redirect()->route('public.home');
    })->name('portal.register');
    Route::post('/portal/register', function () {
        return redirect()->route('public.home');
    })->name('portal.register.submit');

    Route::get('/portal/two-factor-challenge', [MfaController::class, 'showChallenge'])->name('portal.mfa.challenge');
    Route::post('/portal/two-factor-challenge', [MfaController::class, 'verifyChallenge'])->name('portal.mfa.challenge.verify');
});

// ====================================================
// NEW ONBOARDING WORKFLOW - Public Application Routes
// ====================================================
Route::get('/apply', [ApplicationController::class, 'show'])->name('public.application.form');
Route::post('/apply', [ApplicationController::class, 'submit'])->name('public.application.submit');
Route::get('/apply/success', [ApplicationController::class, 'success'])->name('public.application.success');

// Onboarding token-based routes (not authenticated, but token-protected)
Route::get('/onboarding/{token}', [OnboardingController::class, 'show'])
    ->name('participant.onboarding.show');
Route::post('/onboarding/{token}/submit', [OnboardingController::class, 'submit'])
    ->name('participant.onboarding.submit');
Route::get('/onboarding/{token}/agreement/{agreement}', [OnboardingController::class, 'showAgreement'])
    ->name('participant.onboarding.agreement.show');
Route::get('/onboarding/{token}/agreement/{agreement}/download', [OnboardingController::class, 'downloadAgreement'])
    ->name('participant.onboarding.agreement.download');
Route::post('/onboarding/{token}/sign-agreement', [OnboardingController::class, 'signAgreement'])
    ->name('participant.onboarding.sign_agreement');

Route::middleware(['auth'])->group(function () {
    Route::get('/portal/onboarding/status', [ParticipantOnboardingController::class, 'status'])
        ->name('portal.onboarding.status');
    Route::get('/onboarding-status', [OnboardingController::class, 'status'])
        ->name('participant.onboarding.status');
    Route::post('/portal/logout', [AuthController::class, 'logout'])->name('portal.logout');

    // MFA setup and management routes for authenticated users
    Route::get('/portal/mfa/setup', [MfaController::class, 'showSetup'])->name('portal.mfa.setup');
    Route::post('/portal/mfa/confirm', [MfaController::class, 'confirm'])->name('portal.mfa.confirm');
    Route::post('/portal/mfa/disable', [MfaController::class, 'disable'])->name('portal.mfa.disable');
    Route::post('/portal/mfa/regenerate', [MfaController::class, 'regenerateRecoveryCodes'])->name('portal.mfa.regenerate');

    // Push notification subscription endpoints available for any authenticated user with MFA
    Route::get('/portal/push/public-key', [PushSubscriptionController::class, 'publicKey'])->name('portal.push.public_key');
    Route::post('/portal/push/subscription', [PushSubscriptionController::class, 'store'])->name('portal.push.subscription.store');
    Route::delete('/portal/push/subscription', [PushSubscriptionController::class, 'destroy'])->name('portal.push.subscription.destroy');
});

Route::get('/portal/onboarding/{token}', [ParticipantOnboardingController::class, 'show'])
    ->name('portal.onboarding.show');
Route::get('/portal/onboarding/{token}/agreement/{agreement}', [ParticipantOnboardingController::class, 'showAgreement'])
    ->name('portal.onboarding.agreement.show');
Route::get('/portal/onboarding/{token}/agreement/{agreement}/download', [ParticipantOnboardingController::class, 'downloadAgreement'])
    ->name('portal.onboarding.agreement.download');
Route::post('/portal/onboarding/{token}', [ParticipantOnboardingController::class, 'submit'])
    ->name('portal.onboarding.submit');
// Token-based document preview/signing for participants mid-onboarding
Route::get('/portal/onboarding/{token}/document/{document}', [ParticipantOnboardingController::class, 'showOnboardingDocument'])
    ->name('portal.onboarding.document.show');
Route::get('/portal/onboarding/{token}/document/{document}/preview', [ParticipantOnboardingController::class, 'previewOnboardingDocument'])
    ->name('portal.onboarding.document.preview');
Route::get('/portal/onboarding/{token}/document/{document}/download', [ParticipantOnboardingController::class, 'downloadOnboardingDocument'])
    ->name('portal.onboarding.document.download');
// Supporting document download for token-based onboarding (participant must have valid token)
Route::get('/portal/onboarding/{token}/supporting/{id}/download', [ParticipantOnboardingController::class, 'downloadSupportingDocument'])
    ->name('portal.onboarding.supporting.download');
Route::post('/portal/onboarding/{token}/supporting/{id}/view', [ParticipantOnboardingController::class, 'markSupportingViewed'])
    ->name('portal.onboarding.supporting.view');
Route::get('/portal/onboarding/{token}/supporting/status', [ParticipantOnboardingController::class, 'supportingViewStatus'])
    ->name('portal.onboarding.supporting.status');
Route::post('/portal/onboarding/{token}/document/{document}/sign', [ParticipantOnboardingController::class, 'signOnboardingDocument'])
    ->name('portal.onboarding.document.sign');
// ====================================================
// WORKER ONBOARDING WORKFLOW - Public Token-based Routes
// ====================================================
Route::get('/worker/onboarding/{token}', [WorkerOnboardingController::class, 'show'])
    ->name('worker.onboarding.show');
Route::post('/worker/onboarding/{token}/stage1', [WorkerOnboardingController::class, 'submitStage1'])
    ->name('worker.onboarding.stage1.submit');
Route::post('/worker/onboarding/{token}/stage2', [WorkerOnboardingController::class, 'submitStage2'])
    ->name('worker.onboarding.stage2.submit');
Route::post('/worker/onboarding/{token}/stage3/proceed', [WorkerOnboardingController::class, 'proceedStage3'])
    ->name('worker.onboarding.stage3.proceed');
Route::get('/worker/onboarding/{token}/document/{document}/preview', [WorkerOnboardingController::class, 'previewDocument'])
    ->name('worker.onboarding.document.preview');
Route::get('/worker/onboarding/{token}/document/{document}/download', [WorkerOnboardingController::class, 'downloadDocument'])
    ->name('worker.onboarding.document.download');
Route::get('/worker/onboarding/{token}/assigned-document/{document}/preview', [WorkerOnboardingController::class, 'previewAssignedDocument'])
    ->name('worker.onboarding.assigned_document.preview');
Route::get('/worker/onboarding/{token}/assigned-document/{document}/download', [WorkerOnboardingController::class, 'downloadAssignedDocument'])
    ->name('worker.onboarding.assigned_document.download');
Route::post('/worker/onboarding/{token}/stage4', [WorkerOnboardingController::class, 'submitStage4'])
    ->name('worker.onboarding.stage4.submit');
Route::middleware(['auth', 'mfa', 'onboarding_complete'])->group(function () {
    Route::get('/portal/participant/documents', [DocumentController::class, 'indexForParticipant'])->name('portal.participant.documents.index');
    Route::get('/portal/participant/documents/pending', [DocumentController::class, 'pendingSignaturesForParticipant'])->name('portal.participant.documents.pending');
    Route::get('/portal/participant/documents/create', [DocumentController::class, 'indexForParticipant'])->name('portal.participant.documents.create');
    Route::get('/portal/participant/documents/{document}', [DocumentController::class, 'showForParticipant'])->name('portal.participant.documents.show');
    Route::get('/portal/participant/documents/{document}/preview', [DocumentController::class, 'previewForParticipant'])->name('portal.participant.documents.preview');
    Route::post('/portal/participant/documents/{document}/versions', [DocumentController::class, 'uploadVersionForParticipant'])->name('portal.participant.documents.versions.store');
    Route::get('/portal/participant/documents/{document}/versions/{version}/download', [DocumentController::class, 'downloadVersionForParticipant'])->name('portal.participant.documents.versions.download');
    Route::post('/portal/participant/documents/{document}/sign', [DocumentController::class, 'signForParticipant'])->name('portal.participant.documents.sign');
    Route::post('/portal/participant/documents', [DocumentController::class, 'storeForParticipant'])->name('portal.participant.documents.store');
    Route::get('/portal/participant/documents/{document}/download', [DocumentController::class, 'download'])->name('portal.participant.documents.download');
    Route::get('/portal/participant/documents/signature/{signature}', [DocumentController::class, 'downloadSignature'])->name('portal.participant.documents.signature.download');
});

Route::middleware(['auth', 'mfa', 'role:admin|system_admin'])->group(function () {
    Route::get('/admin/enquiries/{id}', [AdminEnquiryController::class, 'show'])->name('admin.enquiries.show');

    Route::prefix('/portal/admin/enquiries')->name('portal.admin.enquiries.')->group(function () {
        Route::get('/', [AdminEnquiryController::class, 'index'])->name('index');
        Route::get('/export', [AdminEnquiryController::class, 'export'])->name('export');
        Route::delete('/{enquiry}', [AdminEnquiryController::class, 'destroy'])->name('destroy');
        Route::get('/{enquiry}', [AdminEnquiryController::class, 'show'])->name('show');
        Route::put('/{enquiry}', [AdminEnquiryController::class, 'update'])->name('update');
        Route::post('/{enquiry}/invite', [AdminEnquiryController::class, 'inviteParticipant'])->name('invite_participant');
    });

    Route::get('/portal/admin/users/create', [AuthController::class, 'showAdminCreate'])->name('portal.admin.users.create');
    Route::post('/portal/admin/users', [AuthController::class, 'createUser'])->name('portal.admin.users.store');
    Route::get('/portal/admin/users/{user}/edit', [AdminController::class, 'editUser'])->name('portal.admin.users.edit');
    Route::put('/portal/admin/users/{user}', [AdminController::class, 'updateUser'])->name('portal.admin.users.update');
    Route::post('/portal/admin/users/{user}/dashboard-login', [AdminController::class, 'forceDashboardLogin'])->name('portal.admin.users.dashboard.login');
    Route::delete('/portal/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('portal.admin.users.destroy');
    // Admin dashboard and management
    Route::get('/portal/admin', [AdminDashboardController::class, 'index'])->name('portal.admin.dashboard');

    // ====================================================
    // NEW ONBOARDING WORKFLOW - Admin Routes
    // ====================================================

    // Applications Management
    Route::prefix('/portal/admin/applications')->name('admin.applications.')->group(function () {
        Route::get('/', [AdminApplicationsController::class, 'index'])->name('index');
        Route::get('/{application}', [AdminApplicationsController::class, 'show'])->name('show');
        Route::post('/{application}/approve', [AdminApplicationsController::class, 'approve'])->name('approve');
        Route::post('/{application}/reject', [AdminApplicationsController::class, 'reject'])->name('reject');
        Route::post('/{application}/under-review', [AdminApplicationsController::class, 'markUnderReview'])->name('under_review');
    });

    // Onboarding Submissions Management
    Route::prefix('/portal/admin/onboarding')->name('admin.onboarding.')->group(function () {
        Route::get('/', [AdminOnboardingController::class, 'index'])->name('index');
        Route::get('/{submission}', [AdminOnboardingController::class, 'show'])->name('show');
        Route::post('/{submission}/approve', [AdminOnboardingController::class, 'approve'])->name('approve');
        Route::post('/{submission}/request-changes', [AdminOnboardingController::class, 'requestChanges'])->name('request_changes');
        Route::post('/{submission}/reject', [AdminOnboardingController::class, 'reject'])->name('reject');
        Route::post('/participant/{participant}/activate', [AdminOnboardingController::class, 'activate'])->name('activate');
    });

    // Agreement Management
    Route::prefix('/portal/admin/agreements')->name('admin.agreements.')->group(function () {
        Route::get('/', [AgreementController::class, 'index'])->name('index');
        Route::get('/create', [AgreementController::class, 'create'])->name('create');
        Route::post('/', [AgreementController::class, 'store'])->name('store');
        Route::get('/{agreement}/edit', [AgreementController::class, 'edit'])->name('edit');
        Route::put('/{agreement}', [AgreementController::class, 'update'])->name('update');
        Route::delete('/{agreement}', [AgreementController::class, 'destroy'])->name('destroy');
        Route::post('/{agreement}/assign/{participant}', [AgreementController::class, 'assignToParticipant'])->name('assign');
        Route::post('/{agreement}/remove/{participant}', [AgreementController::class, 'removeFromParticipant'])->name('remove');
        Route::get('/{agreement}/signature/{participant}', [AgreementController::class, 'viewSignature'])->name('view_signature');
    });

    Route::prefix('/portal/admin/system')->name('portal.admin.system.')->group(function () {
        Route::get('/', [SystemAdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users-roles', [SystemAdminController::class, 'usersRoles'])->name('users_roles');
        Route::get('/mfa', [SystemAdminController::class, 'mfaManagement'])->name('mfa');
        Route::get('/permission-groups', [SystemAdminController::class, 'permissionGroups'])->name('permission_groups');
        Route::get('/permission-groups/create', [SystemAdminController::class, 'createPermissionGroup'])->name('permission_groups.create');
        Route::post('/permission-groups', [SystemAdminController::class, 'storePermissionGroup'])->name('permission_groups.store');
        Route::get('/permission-groups/{permissionGroup}/edit', [SystemAdminController::class, 'editPermissionGroup'])->name('permission_groups.edit');
        Route::put('/permission-groups/{permissionGroup}', [SystemAdminController::class, 'updatePermissionGroup'])->name('permission_groups.update');
        Route::delete('/permission-groups/{permissionGroup}', [SystemAdminController::class, 'destroyPermissionGroup'])->name('permission_groups.destroy');
        Route::get('/notification-rules', [SystemAdminController::class, 'notificationRules'])->name('notification_rules');
        Route::post('/notification-rules', [SystemAdminController::class, 'updateNotificationRules'])->name('notification_rules.update');
        Route::get('/data-retention', [SystemAdminController::class, 'dataRetention'])->name('data_retention');
        Route::post('/data-retention', [SystemAdminController::class, 'updateDataRetention'])->name('data_retention.update');
        Route::get('/health', [SystemAdminController::class, 'systemHealth'])->name('health');
    });

    Route::get('/portal/admin/users', [AdminController::class, 'users'])->name('portal.admin.users');
    Route::get('/portal/admin/users/{user}', [AdminController::class, 'showUser'])->name('portal.admin.users.show');
    Route::post('/portal/admin/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('portal.admin.users.status');
    Route::post('/portal/admin/users/{user}/mfa-reset', [MfaController::class, 'resetUserMfa'])->name('portal.admin.users.mfa.reset');

    Route::prefix('/portal/admin/backups')->name('portal.admin.backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('dashboard');
        Route::get('/history', [BackupController::class, 'backupHistory'])->name('history');
        Route::get('/create', [BackupController::class, 'createBackup'])->name('create');
        Route::post('/', [BackupController::class, 'storeBackup'])->name('store');
        Route::get('/restores', [BackupController::class, 'restoreHistory'])->name('restores');
        Route::get('/restores/create', [BackupController::class, 'createRestore'])->name('restores.create');
        Route::post('/restores', [BackupController::class, 'storeRestore'])->name('restores.store');
        Route::get('/tests', [BackupController::class, 'disasterRecoveryTests'])->name('tests');
        Route::get('/tests/create', [BackupController::class, 'createTest'])->name('tests.create');
        Route::post('/tests', [BackupController::class, 'storeTest'])->name('tests.store');
        Route::get('/compliance', [BackupController::class, 'complianceReport'])->name('compliance');
    });

    // Worker Nominations (Admin routes)
    Route::prefix('/portal/admin/nominations')->name('portal.admin.nominations.')->group(function () {
        Route::get('/', [WorkerNominationController::class, 'adminIndex'])->name('index');
        Route::get('/{nomination}', [WorkerNominationController::class, 'adminShow'])->name('show');
        Route::post('/{nomination}/approve', [WorkerNominationController::class, 'approve'])->name('approve');
        Route::post('/{nomination}/reject', [WorkerNominationController::class, 'reject'])->name('reject');
        Route::post('/{nomination}/invite-worker', [WorkerNominationController::class, 'inviteWorker'])->name('invite_worker');
        Route::post('/{nomination}/resend-invitation', [WorkerNominationController::class, 'resendInvitation'])->name('resend_invitation');
        Route::post('/{nomination}/activate', [WorkerNominationController::class, 'activate'])->name('activate');
        Route::post('/{nomination}/status', [WorkerNominationController::class, 'updateStatus'])->name('update_status');
    });

    // Application Assessment & Approval Workflow
    Route::prefix('/portal/admin/assessments')->name('admin.assessments.')->group(function () {
        Route::get('/', [AssessmentController::class, 'dashboard'])->name('dashboard');
        Route::get('/{assessment}', [AssessmentController::class, 'show'])->name('show');
        Route::post('/{assessment}/assign', [AssessmentController::class, 'assign'])->name('assign');
        Route::get('/{assessment}/review', [AssessmentController::class, 'review'])->name('review');
        Route::post('/{assessment}/eligibility', [AssessmentController::class, 'completeEligibilityAssessment'])->name('eligibility.complete');
        Route::post('/{assessment}/suitability', [AssessmentController::class, 'completeSuitabilityAssessment'])->name('suitability.complete');
        Route::post('/{assessment}/funding', [AssessmentController::class, 'completeFundingVerification'])->name('funding.complete');
        Route::post('/{assessment}/note', [AssessmentController::class, 'addNote'])->name('note.add');
        Route::post('/{assessment}/request-information', [AssessmentController::class, 'requestInformation'])->name('request_information');
        Route::get('/{assessment}/approve', [AssessmentController::class, 'approvalForm'])->name('approve.form');
        Route::post('/{assessment}/approve', [AssessmentController::class, 'approve'])->name('approve');
        Route::get('/{assessment}/reject', [AssessmentController::class, 'rejectionForm'])->name('reject.form');
        Route::post('/{assessment}/reject', [AssessmentController::class, 'reject'])->name('reject');
        Route::post('/{assessment}/send-invitation', [AssessmentController::class, 'sendInvitation'])->name('send_invitation');
        Route::post('/{assessment}/activate', [AssessmentController::class, 'activate'])->name('activate');
        Route::get('/{assessment}/status-history', [AssessmentController::class, 'statusHistory'])->name('status_history');
    });

    Route::get('/portal/admin/participants', [AdminController::class, 'participants'])->name('portal.admin.participants');
    Route::get('/portal/admin/participants/create', [AdminController::class, 'createParticipant'])->name('portal.admin.participants.create');
    Route::post('/portal/admin/participants', [AdminController::class, 'storeParticipant'])->name('portal.admin.participants.store');
    Route::get('/portal/admin/participants/{participant}/edit', [AdminController::class, 'editParticipant'])->name('portal.admin.participants.edit');
    Route::put('/portal/admin/participants/{participant}', [AdminController::class, 'updateParticipant'])->name('portal.admin.participants.update');
    Route::post('/portal/admin/participants/{participant}/resend-onboarding', [AdminController::class, 'resendParticipantOnboardingInvitation'])->name('portal.admin.participants.resend_onboarding');
    Route::delete('/portal/admin/participants/{participant}', [AdminController::class, 'destroyParticipant'])->name('portal.admin.participants.destroy');
    Route::get('/portal/admin/participants/{participant}', [AdminController::class, 'showParticipant'])->name('portal.admin.participants.show');
    Route::post('/portal/admin/participants/{participant}/approve', [AdminController::class, 'approveParticipant'])->name('portal.admin.participants.approve');
    Route::post('/portal/admin/participants/{participant}/deactivate', [AdminController::class, 'deactivateParticipant'])->name('portal.admin.participants.deactivate');
    Route::post('/portal/admin/participants/{participant}/reject', [AdminController::class, 'rejectParticipant'])->name('portal.admin.participants.reject');
    Route::post('/portal/admin/participants/{participant}/request-changes', [AdminController::class, 'requestParticipantChanges'])->name('portal.admin.participants.request_changes');
    Route::get('/portal/admin/participants/{participant}/care-notes', [CareNoteController::class, 'indexForParticipant'])->name('portal.admin.participants.care_notes');
    Route::post('/portal/admin/participants/{participant}/care-notes', [CareNoteController::class, 'storeForParticipant'])->name('portal.admin.participants.care_notes.store');
    Route::get('/portal/admin/workers', [AdminController::class, 'workers'])->name('portal.admin.workers');
    Route::get('/portal/admin/workers/create', [AdminController::class, 'createWorker'])->name('portal.admin.workers.create');
    Route::post('/portal/admin/workers', [AdminController::class, 'storeWorker'])->name('portal.admin.workers.store');
    Route::get('/portal/admin/workers/{worker}/edit', [AdminController::class, 'editWorker'])->name('portal.admin.workers.edit');
    Route::put('/portal/admin/workers/{worker}', [AdminController::class, 'updateWorker'])->name('portal.admin.workers.update');
    Route::delete('/portal/admin/workers/{worker}', [AdminController::class, 'destroyWorker'])->name('portal.admin.workers.destroy');
    Route::get('/portal/admin/workers/{worker}', [AdminController::class, 'showWorker'])->name('portal.admin.workers.show');

    // Worker Onboarding Management
    Route::prefix('/portal/admin/worker-onboarding')->name('admin.worker_onboarding.')->group(function () {
        Route::get('/', [AdminWorkerOnboardingController::class, 'index'])->name('index');
        Route::get('/{worker}', [AdminWorkerOnboardingController::class, 'show'])->name('show');
        Route::post('/invite', [AdminWorkerOnboardingController::class, 'inviteWorker'])->name('invite');
        Route::post('/{worker}/stage1/advance', [AdminWorkerOnboardingController::class, 'advanceToStage2'])->name('stage1.advance');
        Route::post('/{worker}/stage2/approve', [AdminWorkerOnboardingController::class, 'approveStage2'])->name('stage2.approve');
        Route::post('/{worker}/stage2/reject', [AdminWorkerOnboardingController::class, 'rejectStage2'])->name('stage2.reject');
        Route::post('/{worker}/stage3/approve', [AdminWorkerOnboardingController::class, 'approveStage3'])->name('stage3.approve');
        Route::post('/{worker}/stage3/verify', [AdminWorkerOnboardingController::class, 'verifyStage3Batch'])->name('stage3.verify');
        Route::post('/{worker}/stage4/approve', [AdminWorkerOnboardingController::class, 'approveStage4'])->name('stage4.approve');
        Route::post('/{worker}/stage5/services', [AdminWorkerOnboardingController::class, 'addServiceApproval'])->name('stage5.services.add');
        Route::post('/{worker}/stage5/approve', [AdminWorkerOnboardingController::class, 'approveStage5'])->name('stage5.approve');
        Route::post('/{worker}/resend-invitation', [AdminWorkerOnboardingController::class, 'resendInvitation'])->name('resend_invitation');
        Route::post('/{worker}/reject', [AdminWorkerOnboardingController::class, 'rejectWorker'])->name('reject');
    });

    Route::prefix('/portal/admin/shifts')->name('portal.admin.shifts.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::get('/create', [ShiftController::class, 'create'])->name('create');
        Route::post('/', [ShiftController::class, 'store'])->name('store');
        Route::get('/{shift}/edit', [ShiftController::class, 'edit'])->name('edit');
        Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
        Route::post('/{shift}/cancel', [ShiftController::class, 'cancel'])->name('cancel');
        Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
    });

    // Compliance Management
    Route::prefix('/portal/admin/compliance')->name('portal.admin.compliance.')->group(function () {
        Route::get('/', [ComplianceController::class, 'index'])->name('index');
        Route::get('/dashboard', [ComplianceController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/stats', [ComplianceController::class, 'dashboardStats'])->name('dashboard.stats');
        Route::get('/report', [ComplianceController::class, 'report'])->name('report');
        Route::get('/export', [ComplianceController::class, 'export'])->name('export');
        Route::get('/workers-needing-attention', [ComplianceController::class, 'workersNeedingAttention'])->name('workers_needing_attention');
        Route::get('/document-types', [ComplianceController::class, 'getDocumentTypes'])->name('document_types');
        Route::post('/scan', [ComplianceController::class, 'scanCompliance'])->name('scan');

        // Document management
        Route::get('/documents', [ComplianceController::class, 'index'])->name('documents.index');
        Route::post('/documents', [ComplianceController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}', [ComplianceController::class, 'show'])->name('documents.show');
        Route::put('/documents/{document}', [ComplianceController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{document}', [ComplianceController::class, 'destroy'])->name('documents.destroy');
        Route::post('/documents/{document}/upload', [ComplianceController::class, 'uploadFile'])->name('documents.upload');
        Route::get('/documents/{document}/download', [ComplianceController::class, 'downloadFile'])->name('documents.download');
        Route::post('/documents/{document}/verify', [ComplianceController::class, 'verify'])->name('documents.verify');
        Route::post('/documents/{document}/reject', [ComplianceController::class, 'reject'])->name('documents.reject');

        // Worker compliance
        Route::get('/workers/{worker}', [ComplianceController::class, 'workerCompliance'])->name('workers.show');
        Route::post('/workers/{worker}/initialize', [ComplianceController::class, 'initializeWorker'])->name('workers.initialize');
        Route::get('/workers/{worker}/assignable', [ComplianceController::class, 'checkAssignability'])->name('workers.check_assignable');
    });

    // Care Review Management
    Route::prefix('/portal/admin/care-reviews')->name('portal.admin.care_reviews.')->group(function () {
        Route::get('/', [CareReviewController::class, 'index'])->name('index');
        Route::post('/', [CareReviewController::class, 'store'])->name('store');
        Route::get('/{review}', [CareReviewController::class, 'show'])->name('show');
        Route::put('/{review}', [CareReviewController::class, 'update'])->name('update');
        Route::post('/{review}/complete', [CareReviewController::class, 'complete'])->name('complete');

        // Dashboard and reporting
        Route::get('/dashboard/stats', [CareReviewController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/due', [CareReviewController::class, 'reviewsDue'])->name('due');
        Route::get('/dashboard/completed', [CareReviewController::class, 'reviewsCompleted'])->name('completed');
        Route::get('/dashboard/overdue', [CareReviewController::class, 'reviewsOverdue'])->name('overdue');
        Route::get('/dashboard/workload', [CareReviewController::class, 'careManagerWorkload'])->name('workload');

        // Reports
        Route::get('/report/outstanding', [CareReviewController::class, 'outstandingReport'])->name('report.outstanding');
        Route::get('/report/monthly', [CareReviewController::class, 'monthlyReport'])->name('report.monthly');
        Route::get('/export/outstanding', [CareReviewController::class, 'exportOutstandingReport'])->name('export.outstanding');
        Route::get('/export/monthly', [CareReviewController::class, 'exportMonthlyReport'])->name('export.monthly');

        // Participant history and references
        Route::get('/participant/{participant}/history', [CareReviewController::class, 'participantHistory'])->name('participant.history');
        Route::get('/review-types', [CareReviewController::class, 'getReviewTypes'])->name('types');
        Route::get('/statuses', [CareReviewController::class, 'getReviewStatuses'])->name('statuses');
        Route::get('/{review}/activity', [CareReviewController::class, 'activityLog'])->name('activity');
    });

    // Reporting & Export Management
    Route::prefix('/portal/admin/reports')->name('portal.admin.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/available', [ReportController::class, 'availableReports'])->name('available');
        Route::post('/export', [ReportController::class, 'exportReport'])->name('export');
        Route::get('/history', [ReportController::class, 'exportHistory'])->name('history');
        Route::get('/statistics', [ReportController::class, 'reportStatistics'])->name('statistics');
        Route::post('/preview', [ReportController::class, 'previewReport'])->name('preview');
    });

    Route::get('/portal/admin/assignments', [AdminController::class, 'assignments'])->name('portal.admin.assignments');
    Route::get('/portal/admin/assignments/create', [AdminController::class, 'createAssignment'])->name('portal.admin.assignments.create');
    Route::post('/portal/admin/assignments', [AdminController::class, 'storeAssignment'])->name('portal.admin.assignments.store');
    Route::get('/portal/admin/assignments/{assignment}', [AdminController::class, 'showAssignment'])->name('portal.admin.assignments.show');
    Route::get('/portal/admin/assignments/{assignment}/edit', [AdminController::class, 'editAssignment'])->name('portal.admin.assignments.edit');
    Route::put('/portal/admin/assignments/{assignment}', [AdminController::class, 'updateAssignment'])->name('portal.admin.assignments.update');
    Route::delete('/portal/admin/assignments/{assignment}', [AdminController::class, 'destroyAssignment'])->name('portal.admin.assignments.destroy');

    Route::get('/portal/admin/budgets', [AdminController::class, 'budgets'])->name('portal.admin.budgets');
    Route::post('/portal/admin/assign-worker', [AdminController::class, 'assignWorker'])->name('portal.admin.assign_worker');
    Route::get('/portal/admin/pre-approvals', [AdminController::class, 'preApprovals'])->name('portal.admin.pre_approvals');
    Route::get('/portal/admin/pre-approvals/{preApprovalRequest}', [AdminController::class, 'showPreApproval'])->name('portal.admin.pre_approvals.show');
    Route::get('/portal/admin/pre-approvals/{preApprovalRequest}/quote', [AdminController::class, 'downloadPreApprovalQuote'])->name('portal.admin.pre_approvals.quote.download');
    Route::get('/portal/admin/pre-approvals/{preApprovalRequest}/attachments/{attachment}/download', [AdminController::class, 'downloadPreApprovalAttachment'])->name('portal.admin.pre_approvals.attachments.download');
    Route::post('/portal/admin/pre-approvals/{preApprovalRequest}/approve', [AdminController::class, 'approvePreApproval'])->name('portal.admin.pre_approvals.approve');
    Route::post('/portal/admin/pre-approvals/{preApprovalRequest}/request-info', [AdminController::class, 'requestPreApprovalInfo'])->name('portal.admin.pre_approvals.request_info');
    Route::post('/portal/admin/pre-approvals/{preApprovalRequest}/reject', [AdminController::class, 'rejectPreApproval'])->name('portal.admin.pre_approvals.reject');
    Route::post('/portal/admin/pre-approvals/{preApprovalRequest}/cancel', [AdminController::class, 'cancelPreApproval'])->name('portal.admin.pre_approvals.cancel');
    Route::get('/portal/admin/care-notes', [AdminController::class, 'careNotes'])->name('portal.admin.care_notes');
    Route::get('/portal/admin/care-notes/{careNote}', [AdminController::class, 'showCareNote'])->name('portal.admin.care_notes.show');
    Route::post('/portal/admin/care-notes/{careNote}/approve', [AdminController::class, 'approveCareNote'])->name('portal.admin.care_notes.approve');
    Route::get('/portal/admin/incidents', [AdminController::class, 'incidents'])->name('portal.admin.incidents');
    Route::get('/portal/admin/incidents/{incident}', [AdminController::class, 'showIncident'])->name('portal.admin.incidents.show');
    Route::post('/portal/admin/incidents/{incident}/status', [AdminController::class, 'updateIncidentStatus'])->name('portal.admin.incidents.status');
    Route::get('/portal/admin/invoices', [AdminController::class, 'invoices'])->name('portal.admin.invoices');
    Route::get('/portal/admin/invoices/{invoice}', [AdminController::class, 'showInvoice'])->name('portal.admin.invoices.show');
    Route::post('/portal/admin/invoices/{invoice}/review', [AdminController::class, 'reviewInvoice'])->name('portal.admin.invoices.review');
    Route::post('/portal/admin/invoices/{invoice}/reject', [AdminController::class, 'rejectInvoice'])->name('portal.admin.invoices.reject');
    Route::post('/portal/admin/invoices/{invoice}/pay', [AdminController::class, 'payInvoice'])->name('portal.admin.invoices.pay');
    Route::get('/portal/admin/invoices/{invoice}/attachment/download', [AdminController::class, 'downloadInvoiceAttachment'])->name('portal.admin.invoices.attachment.download');
    Route::get('/portal/admin/budgets/{budget}/export-pdf', [AdminController::class, 'exportBudgetPdf'])->name('portal.admin.budgets.export-pdf');
    Route::get('/portal/admin/documents', [AdminController::class, 'documents'])->name('portal.admin.documents');
    Route::get('/portal/admin/documents/create', [AdminController::class, 'createDocument'])->name('portal.admin.documents.create');
    Route::post('/portal/admin/documents', [AdminController::class, 'storeDocument'])->name('portal.admin.documents.store');
    Route::get('/portal/admin/documents/{document}', [AdminController::class, 'showDocument'])->name('portal.admin.documents.show');
    Route::post('/portal/admin/documents/{document}/toggle-onboarding', [AdminController::class, 'toggleDocumentOnboarding'])->name('portal.admin.documents.toggle_onboarding');
    Route::post('/portal/admin/documents/{document}/versions', [AdminController::class, 'uploadDocumentVersion'])->name('portal.admin.documents.versions.store');
    Route::get('/portal/admin/documents/{document}/preview', [AdminController::class, 'previewDocument'])->name('portal.admin.documents.preview');
    Route::get('/portal/admin/documents/{document}/download', [AdminController::class, 'downloadDocument'])->name('portal.admin.documents.download');
    Route::get('/portal/admin/documents/{document}/signatures/{signature}/download', [AdminController::class, 'downloadDocumentSignature'])->name('portal.admin.documents.signatures.download');
    Route::get('/portal/admin/documents/{document}/signatures/{signature}/certificate', [AdminController::class, 'downloadDocumentCertificate'])->name('portal.admin.documents.signatures.certificate.download');
    Route::get('/portal/admin/documents/{document}/versions/{version}/download', [AdminController::class, 'downloadDocumentVersion'])->name('portal.admin.documents.versions.download');
    Route::get('/portal/admin/settings', [AdminController::class, 'settings'])->name('portal.admin.settings');
    Route::post('/portal/admin/settings', [AdminController::class, 'updateSettings'])->name('portal.admin.settings.update');
    Route::get('/portal/admin/legal', [AdminController::class, 'legalDocuments'])->name('portal.admin.legal');
    Route::post('/portal/admin/legal', [AdminController::class, 'updateLegalDocuments'])->name('portal.admin.legal.update');
    Route::post('/portal/admin/settings/generate-vapid-keys', [AdminController::class, 'generateVapidKeys'])->name('portal.admin.settings.generate_vapid_keys');
    Route::post('/portal/admin/settings/clear-cache', [AdminController::class, 'clearCache'])->name('portal.admin.settings.clear_cache');
    Route::post('/portal/admin/settings/test-email', [AdminController::class, 'sendTestEmail'])->name('portal.admin.settings.test_email');
    Route::get('/portal/admin/activity', [AdminController::class, 'activity'])->name('portal.admin.activity');
    Route::get('/portal/admin/reports', [AdminController::class, 'reports'])->name('portal.admin.reports');
    Route::get('/portal/admin/reports/{type}/export', [AdminController::class, 'exportReport'])->name('portal.admin.reports.export');

    // Message Management
    Route::prefix('/portal/admin/messages')->name('portal.admin.messages.')->group(function () {
        // Templates
        Route::get('/templates', [MessageController::class, 'templatesIndex'])->name('templates.index');
        Route::get('/templates/create', [MessageController::class, 'createTemplate'])->name('templates.create');
        Route::post('/templates', [MessageController::class, 'storeTemplate'])->name('templates.store');
        Route::get('/templates/{template}/edit', [MessageController::class, 'editTemplate'])->name('templates.edit');
        Route::put('/templates/{template}', [MessageController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('/templates/{template}', [MessageController::class, 'deleteTemplate'])->name('templates.delete');

        // Email Templates
        Route::prefix('/email-templates')->name('email_templates.')->group(function () {
            Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
            Route::get('/create', [EmailTemplateController::class, 'create'])->name('create');
            Route::post('/', [EmailTemplateController::class, 'store'])->name('store');
            Route::get('/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('edit');
            Route::put('/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('update');
            Route::delete('/{emailTemplate}', [EmailTemplateController::class, 'destroy'])->name('delete');
            Route::get('/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
            Route::post('/{emailTemplate}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('duplicate');
            Route::post('/{emailTemplate}/versions/{version}/restore', [EmailTemplateController::class, 'restoreVersion'])->name('versions.restore');
            Route::post('/{emailTemplate}/send-test', [EmailTemplateController::class, 'sendTestEmail'])->name('send_test');
        });

        // Send messages
        Route::get('/send', [MessageController::class, 'sendIndex'])->name('send.index');
        Route::post('/send', [MessageController::class, 'sendMessage'])->name('send.store');

        // Broadcast messages
        Route::get('/broadcast', [MessageController::class, 'broadcastIndex'])->name('broadcast.index');
        Route::post('/broadcast', [MessageController::class, 'broadcast'])->name('broadcast.store');

        // Sent and inbox
        Route::get('/sent', [MessageController::class, 'sent'])->name('sent');
    });

    // Support Center - Tickets & Live Chat
    Route::prefix('/portal/admin/support')->name('portal.admin.support.')->group(function () {
        Route::get('/', [SupportCenterController::class, 'dashboard'])->name('dashboard');

        // Support Tickets
        Route::get('/tickets', [SupportCenterController::class, 'ticketsIndex'])->name('tickets');
        Route::get('/tickets/{ticket}', [SupportCenterController::class, 'ticketShow'])->name('ticket.show');
        Route::post('/tickets/{ticket}/response', [SupportCenterController::class, 'ticketResponse'])->name('ticket.response');
        Route::post('/tickets/{ticket}/status', [SupportCenterController::class, 'ticketStatus'])->name('ticket.status');

        // Support Conversations (Live Chat)
        Route::get('/conversations', [SupportCenterController::class, 'conversationsIndex'])->name('conversations');
        Route::get('/conversations/create', [SupportCenterController::class, 'conversationCreate'])->name('conversation.create');
        Route::post('/conversations', [SupportCenterController::class, 'conversationStore'])->name('conversation.store');
        Route::get('/conversations/{conversation}', [SupportCenterController::class, 'conversationShow'])->name('conversation.show');
        Route::get('/conversations/{conversation}/messages', [SupportCenterController::class, 'conversationMessages'])->name('conversation.messages');
        Route::post('/conversations/{conversation}/message', [SupportCenterController::class, 'conversationMessage'])->name('conversation.message');
        Route::post('/conversations/{conversation}/status', [SupportCenterController::class, 'conversationStatus'])->name('conversation.status');
    });
});

Route::middleware(['auth', 'mfa', 'role:worker', 'onboarding_complete'])->prefix('/portal/worker')->name('portal.worker.')->group(function () {
    Route::get('/dashboard', [WorkerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/participants', [WorkerPortalController::class, 'assignedParticipants'])->name('assigned_participants');
    Route::get('/participants/{participant}', [WorkerPortalController::class, 'showParticipant'])->name('participants.show');
    Route::get('/shifts', [WorkerPortalController::class, 'shifts'])->name('shifts');
    Route::post('/shifts/{shift}/confirm', [WorkerPortalController::class, 'confirmShift'])->name('shifts.confirm');
    Route::post('/shifts/{shift}/start', [WorkerPortalController::class, 'startShift'])->name('shifts.start');
    Route::post('/shifts/{shift}/complete', [WorkerPortalController::class, 'completeShift'])->name('shifts.complete');
    Route::get('/care-notes/create', [WorkerPortalController::class, 'createCareNote'])->name('care_notes.create');
    Route::post('/care-notes', [WorkerPortalController::class, 'storeCareNote'])->name('care_notes.store');
    Route::get('/incidents/create', [WorkerPortalController::class, 'createIncident'])->name('incidents.create');
    Route::post('/incidents', [WorkerPortalController::class, 'storeIncident'])->name('incidents.store');
    Route::get('/documents/upload', [WorkerPortalController::class, 'uploadDocuments'])->name('documents.upload');
    Route::post('/documents', [WorkerPortalController::class, 'storeDocument'])->name('documents.store');
    Route::get('/invoices', [WorkerPortalController::class, 'invoices'])->name('invoices');
    Route::post('/invoices', [WorkerPortalController::class, 'storeInvoice'])->name('invoices.store');
    Route::get('/invoices/{invoice}/download', [WorkerPortalController::class, 'downloadInvoice'])->name('invoices.download');
    Route::get('/forms', [WorkerPortalController::class, 'forms'])->name('forms');
    Route::get('/forms/{document}/download', [WorkerPortalController::class, 'downloadForm'])->name('forms.download');
    Route::get('/forms/{document}', [WorkerPortalController::class, 'showForm'])->name('forms.show');
    Route::post('/forms/{document}/sign', [WorkerPortalController::class, 'signForm'])->name('forms.sign');
    Route::get('/profile', [WorkerPortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [WorkerPortalController::class, 'updateProfile'])->name('profile.update');
});

Route::middleware(['auth', 'mfa', 'assessment_workflow'])->group(function () {
    Route::get('/portal/gallery', [DocumentController::class, 'gallery'])->name('portal.gallery');
    Route::get('/portal/gallery/{document}/preview', [DocumentController::class, 'previewGallery'])->name('portal.gallery.preview');
    Route::get('/portal/gallery/{document}/download', [DocumentController::class, 'downloadGallery'])->name('portal.gallery.download');
});

Route::middleware(['auth', 'mfa', 'onboarding_complete', 'assessment_workflow'])->group(function () {
    Route::get('/portal/dashboard', [AuthController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/portal/profile', [AuthController::class, 'profile'])->name('portal.profile');
    Route::put('/portal/profile', [AuthController::class, 'updateProfile'])->name('portal.profile.update');

    Route::get('/portal/notifications', [NotificationController::class, 'index'])->name('portal.notifications');
    Route::get('/portal/notifications/{notification}/open', [NotificationController::class, 'show'])->name('portal.notifications.show');
    Route::post('/portal/notifications/mark-read-all', [NotificationController::class, 'markAllRead'])->name('portal.notifications.mark_all_read');
    Route::post('/portal/notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('portal.notifications.mark_read');
    Route::post('/portal/notifications/{notification}/mark-unread', [NotificationController::class, 'markUnread'])->name('portal.notifications.mark_unread');
    Route::get('/portal/notifications/preferences', [NotificationController::class, 'preferences'])->name('portal.notifications.preferences');
    Route::post('/portal/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('portal.notifications.preferences.update');

    // Pre-approvals
    Route::get('/portal/participant/pre-approvals', [PreApprovalController::class, 'index'])->name('portal.participant.pre_approvals.index');
    Route::get('/portal/participant/pre-approvals/{preApprovalRequest}/quote', [PreApprovalController::class, 'downloadQuote'])->name('portal.participant.pre_approvals.quote.download');
    Route::get('/portal/participant/pre-approvals/{preApprovalRequest}/attachments/{attachment}/download', [PreApprovalController::class, 'downloadAttachment'])->name('portal.participant.pre_approvals.attachments.download');
    Route::post('/portal/participant/pre-approvals', [PreApprovalController::class, 'storeForParticipant'])->name('portal.participant.pre_approvals.store');
    Route::post('/portal/participant/pre-approvals/{preApprovalRequest}', [PreApprovalController::class, 'updateForParticipant'])->name('portal.participant.pre_approvals.update');

    // Generic pre-approval workflow routes
    Route::post('/pre-approvals', [PreApprovalController::class, 'storeForParticipant'])->name('pre_approvals.store');
    Route::post('/pre-approvals/{preApprovalRequest}/approve', [AdminController::class, 'approvePreApproval'])->name('pre_approvals.approve');
    Route::post('/pre-approvals/{preApprovalRequest}/reject', [AdminController::class, 'rejectPreApproval'])->name('pre_approvals.reject');

    // Invoices
    Route::get('/portal/participant/invoices', [InvoiceController::class, 'index'])->name('portal.participant.invoices.index');
    Route::post('/portal/participant/invoices', [InvoiceController::class, 'storeForParticipant'])->name('portal.participant.invoices.store');
    Route::get('/portal/participant/invoices/{invoice}/download', [InvoiceController::class, 'downloadAttachment'])->name('portal.participant.invoices.download');

    // Care notes (participants can view; workers create care notes)
    Route::get('/portal/participant/care-notes', [CareNoteController::class, 'index'])->name('portal.participant.care_notes.index');
    Route::post('/portal/participant/checklist', [CareNoteController::class, 'storeChecklist'])->name('portal.participant.checklist.store');

    // Complaints (kept on ParticipantPortalController for now)
    Route::get('/portal/participant/complaints', [ParticipantPortalController::class, 'showComplaints'])->name('portal.participant.complaints.create');
    Route::post('/portal/participant/complaints', [ParticipantPortalController::class, 'storeComplaint'])->name('portal.participant.complaints.store');

    // Budget
    Route::get('/portal/participant/budget', [ParticipantPortalController::class, 'showBudget'])->name('portal.participant.budget');
    Route::get('/portal/participant/services', [ParticipantPortalController::class, 'showServices'])->name('portal.participant.services');
    Route::get('/portal/participant/team', [ParticipantPortalController::class, 'showTeam'])->name('portal.participant.team');
    Route::get('/portal/participant/feedback', [ParticipantPortalController::class, 'showComplaints'])->name('portal.participant.feedback');

    // Worker Nominations (Participant routes)
    // Restrict to auth + mfa only to avoid middleware (onboarding/assessment)
    // unintentionally blocking participant nominations on some hosts.
    Route::prefix('/portal/participant/nominations')->name('portal.participant.nominations.')->middleware(['auth', 'mfa'])->group(function () {
        Route::get('/', [WorkerNominationController::class, 'index'])->name('index');
        Route::get('/create', [WorkerNominationController::class, 'create'])->name('create');
        Route::post('/', [WorkerNominationController::class, 'store'])->name('store');
        Route::get('/{nomination}', [WorkerNominationController::class, 'show'])->name('show');
        Route::delete('/{nomination}', [WorkerNominationController::class, 'destroy'])->name('destroy');
    });

    Route::get('/budget/{participant}', [BudgetController::class, 'show'])->name('budget.show');

    // Budget management
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');

    Route::post('/budgets/{budget}/transactions', [BudgetTransactionController::class, 'store'])->name('budgets.transactions.store');

    Route::get('/budgets/{budget}/export/csv', [BudgetReportController::class, 'exportCsv'])->name('budgets.export.csv');
    Route::get('/budgets/{budget}/export/pdf', [BudgetReportController::class, 'exportPdf'])->name('budgets.export.pdf');

    // Messages
    Route::prefix('/portal/messages')->name('portal.messages.')->group(function () {
        Route::get('/compose/{recipient?}', [MessageController::class, 'compose'])
            ->where('recipient', '[0-9]+')
            ->name('compose');
        Route::get('/chat/{recipient}', [MessageController::class, 'conversation'])
            ->where('recipient', '[0-9]+')
            ->name('conversation');
        Route::get('/chat/{recipient}/messages', [MessageController::class, 'conversationMessages'])
            ->where('recipient', '[0-9]+')
            ->name('conversation.messages');
        Route::post('/chat/{recipient}', [MessageController::class, 'conversationSend'])
            ->where('recipient', '[0-9]+')
            ->name('conversation.send');
        Route::post('/send', [MessageController::class, 'send'])->name('send');
        Route::get('/inbox', [MessageController::class, 'inbox'])->name('inbox');
        Route::get('/from-message/{message}', [MessageController::class, 'conversationFromMessage'])->name('conversation.from_message');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/mark-read', [MessageController::class, 'markRead'])->name('mark_read');
        Route::post('/{message}/mark-unread', [MessageController::class, 'markUnread'])->name('mark_unread');
        Route::post('/{message}/delete', [MessageController::class, 'delete'])->name('delete');
    });

    // Support Tickets
    Route::prefix('/portal/support')->name('portal.support.')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index');
        Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
        Route::post('/', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show')->where('ticket', '[0-9]+');
        Route::post('/{ticket}/response', [SupportTicketController::class, 'addResponse'])->name('add-response')->where('ticket', '[0-9]+');
        Route::post('/{ticket}/close', [SupportTicketController::class, 'close'])->name('close')->where('ticket', '[0-9]+');
        Route::post('/{ticket}/reopen', [SupportTicketController::class, 'reopen'])->name('reopen')->where('ticket', '[0-9]+');
    });

    Route::prefix('/portal/support/conversations')->name('portal.support.conversations.')->middleware('auth')->group(function () {
        Route::get('/', [SupportConversationController::class, 'index'])->name('index');
        Route::get('/{conversation}', [SupportConversationController::class, 'show'])->name('show');
        Route::get('/{conversation}/messages', [SupportConversationController::class, 'messages'])->name('messages');
        Route::post('/{conversation}/message', [SupportConversationController::class, 'sendMessage'])->name('message');
    });
});

Route::post('/support/widget', [SupportTicketController::class, 'storeFromWidget'])
    ->name('public.support.widget.store');
Route::post('/support/widget/auth', [SupportTicketController::class, 'storeFromWidgetAuthenticated'])
    ->name('support.widget.store.authenticated')->middleware('auth');

Route::get('/portal/force-login/{user}/{token}', [AuthController::class, 'forceLogin'])
    ->name('portal.force_login');
Route::get('/support/widget/{conversation}', [SupportTicketController::class, 'showWidgetConversation'])
    ->name('public.support.widget.show');
Route::post('/support/widget/{conversation}/message', [SupportTicketController::class, 'widgetConversationMessage'])
    ->name('public.support.widget.message');

Route::get('/support/widget/{conversation}/view', [SupportTicketController::class, 'showWidgetView'])
    ->name('public.support.widget.view');

Route::fallback(function (Request $request) {
    if ($request->expectsJson()) {
        return response()->json(['message' => 'Not found.'], 404);
    }

    if (Auth::check()) {
        return redirect()->route('portal.dashboard');
    }

    return redirect()->route('public.home');
});
