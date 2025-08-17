<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, 
        Notifiable, 
        HasApiTokens, 
        HasRoles, 
        SoftDeletes, 
        LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'stripe_customer_id',
        'failed_login_attempts',
        'locked_until',
        'hipaa_acknowledged',
        'hipaa_acknowledged_at',
        'hipaa_acknowledgment_ip',
        'license_number',
        'license_state',
        'license_expiry',
        'dea_number',
        'npi_number',
        'pharmacy_id',
        'phone',
        'date_of_birth',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'failed_login_attempts' => 'integer',
        'locked_until' => 'datetime',
        'hipaa_acknowledged' => 'boolean',
        'hipaa_acknowledged_at' => 'datetime',
        'license_expiry' => 'date',
        'date_of_birth' => 'date',
        'two_factor_recovery_codes' => 'array',
    ];

    /**
     * User types constants
     */
    public const TYPE_PATIENT = 'patient';
    public const TYPE_PHARMACIST = 'pharmacist';
    public const TYPE_ADMIN = 'admin';
    public const TYPE_PRESCRIBER = 'prescriber';

    /**
     * Get all user types
     */
    public static function getUserTypes(): array
    {
        return [
            self::TYPE_PATIENT,
            self::TYPE_PHARMACIST,
            self::TYPE_ADMIN,
            self::TYPE_PRESCRIBER,
        ];
    }

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the user's carts.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the user's orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user's prescriptions.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    /**
     * Get all payment methods for this user.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get all payments for this user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the user who created this user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this user.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this user.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Check if user is a patient.
     */
    public function isPatient(): bool
    {
        return $this->user_type === self::TYPE_PATIENT;
    }

    /**
     * Check if user is a pharmacist.
     */
    public function isPharmacist(): bool
    {
        return $this->user_type === self::TYPE_PHARMACIST;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    /**
     * Check if user is a prescriber.
     */
    public function isPrescriber(): bool
    {
        return $this->user_type === self::TYPE_PRESCRIBER;
    }

    /**
     * Check if user is a healthcare provider.
     */
    public function isHealthcareProvider(): bool
    {
        return in_array($this->user_type, [self::TYPE_PHARMACIST, self::TYPE_PRESCRIBER]);
    }

    /**
     * Check if user account is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if user has acknowledged HIPAA.
     */
    public function hasAcknowledgedHipaa(): bool
    {
        return $this->hipaa_acknowledged && $this->hipaa_acknowledged_at;
    }

    /**
     * Check if user has valid license (for healthcare providers).
     */
    public function hasValidLicense(): bool
    {
        if (!$this->isHealthcareProvider()) {
            return true; // Not applicable
        }

        return $this->license_number && 
               $this->license_state && 
               $this->license_expiry && 
               $this->license_expiry->isFuture();
    }

    /**
     * Check if user has two-factor authentication enabled and confirmed.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && 
               $this->two_factor_secret && 
               $this->two_factor_confirmed_at;
    }

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');
        
        // Lock account after 5 failed attempts for 30 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount(30);
        }
    }

    /**
     * Reset failed login attempts.
     */
    public function resetFailedLogins(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Lock the user account for specified minutes.
     */
    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Acknowledge HIPAA compliance.
     */
    public function acknowledgeHipaa(?string $ip = null): void
    {
        $this->update([
            'hipaa_acknowledged' => true,
            'hipaa_acknowledged_at' => now(),
            'hipaa_acknowledgment_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Generate two-factor authentication secret.
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        
        $this->update([
            'two_factor_secret' => encrypt($secret),
        ]);
        
        return $secret;
    }

    /**
     * Get two-factor authentication QR code.
     */
    public function getTwoFactorQrCode(): string
    {
        $google2fa = app('pragmarx.google2fa');
        $secret = decrypt($this->two_factor_secret);
        
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $secret
        );
    }

    /**
     * Confirm two-factor authentication.
     */
    public function confirmTwoFactor(string $code): bool
    {
        $google2fa = app('pragmarx.google2fa');
        $secret = decrypt($this->two_factor_secret);
        
        if ($google2fa->verifyKey($secret, $code)) {
            $this->update([
                'two_factor_enabled' => true,
                'two_factor_confirmed_at' => now(),
                'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Generate recovery codes for two-factor authentication.
     */
    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = str_replace('-', '', \Illuminate\Support\Str::uuid());
        }
        return $codes;
    }

    /**
     * Use a recovery code.
     */
    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->two_factor_recovery_codes ?? [];
        
        if (($key = array_search($code, $codes)) !== false) {
            unset($codes[$key]);
            $this->update(['two_factor_recovery_codes' => array_values($codes)]);
            return true;
        }
        
        return false;
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'user_type',
                'is_active',
                'last_login_at',
                'failed_login_attempts',
                'hipaa_acknowledged',
                'license_number',
                'license_state',
                'license_expiry',
                'two_factor_enabled',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}: {$this->email}");
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }
}
