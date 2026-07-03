<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($participant = $user->participant) {
                $participant->agreements()->detach();
            }
        });
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'force_dashboard',
        'password',
        'mfa_enabled',
        'mfa_enrolled_at',
        'timezone',
        'last_login_at',
        'password_changed_at',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'mfa_enrolled_at' => 'datetime',
            'force_dashboard' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        if (! $this->profile_photo_path) {
            return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0E3863&color=fff&rounded=true';
        }

        $path = ltrim($this->profile_photo_path, '/');

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0E3863&color=fff&rounded=true';
    }

    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class);
    }

    public function worker(): HasOne
    {
        return $this->hasOne(Worker::class);
    }

    public function supportPerson(): HasOne
    {
        return $this->hasOne(SupportPerson::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by_id');
    }

    public function assignedSignatureRequests(): HasMany
    {
        return $this->hasMany(SignatureRequest::class, 'assigned_user_id');
    }

    public function createdSignatureRequests(): HasMany
    {
        return $this->hasMany(SignatureRequest::class, 'assigned_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function portalNotifications(): HasMany
    {
        return $this->hasMany(PortalNotification::class);
    }

    public function unreadPortalNotifications(): HasMany
    {
        return $this->portalNotifications()->whereNull('read_at');
    }

    public function managedReviews(): HasMany
    {
        return $this->hasMany(MonthlyCareReview::class, 'care_manager_id');
    }

    public function managedContactLogs(): HasMany
    {
        return $this->hasMany(CareContactLog::class, 'care_manager_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function backupRestoreLogs(): HasMany
    {
        return $this->hasMany(RestoreRecord::class, 'initiated_by_id');
    }

    public function disasterRecoveryTests(): HasMany
    {
        return $this->hasMany(DisasterRecoveryTest::class, 'conducted_by_id');
    }

    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function hasRole(string|array $role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role, true);
        }

        return $this->role === $role;
    }
}
