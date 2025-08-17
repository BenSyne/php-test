<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'preferred_name',
        'gender',
        'ssn_encrypted',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone_mobile',
        'phone_work',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'allergies',
        'medical_conditions',
        'current_medications',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_group_number',
        'insurance_expiry',
        'specialization',
        'medical_school',
        'graduation_year',
        'certifications',
        'bio',
        'consultation_fee',
        'preferred_pharmacy_id',
        'consent_to_text',
        'consent_to_email',
        'consent_to_marketing',
        'avatar_path',
        'profile_visibility',
        'privacy_policy_accepted_at',
        'terms_accepted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allergies' => 'array',
        'medical_conditions' => 'array',
        'current_medications' => 'array',
        'certifications' => 'array',
        'insurance_expiry' => 'date',
        'privacy_policy_accepted_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'consent_to_text' => 'boolean',
        'consent_to_email' => 'boolean',
        'consent_to_marketing' => 'boolean',
        'profile_visibility' => 'boolean',
        'graduation_year' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'ssn_encrypted',
        'insurance_policy_number',
        'insurance_group_number',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this profile.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this profile.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this profile.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: $this->user->name ?? 'Unknown';
    }

    /**
     * Get the display name attribute (preferred name or full name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->preferred_name ?: $this->full_name;
    }

    /**
     * Get the full address attribute.
     */
    public function getFullAddressAttribute(): string
    {
        $address = collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country !== 'US' ? $this->country : null,
        ])->filter()->implode(', ');

        return $address;
    }

    /**
     * Check if the profile belongs to a patient.
     */
    public function isPatient(): bool
    {
        return $this->user?->user_type === 'patient';
    }

    /**
     * Check if the profile belongs to a healthcare provider.
     */
    public function isHealthcareProvider(): bool
    {
        return in_array($this->user?->user_type, ['pharmacist', 'prescriber']);
    }

    /**
     * Check if the profile has completed medical information.
     */
    public function hasMedicalInfo(): bool
    {
        return !empty($this->allergies) || 
               !empty($this->medical_conditions) || 
               !empty($this->current_medications);
    }

    /**
     * Check if the profile has insurance information.
     */
    public function hasInsurance(): bool
    {
        return !empty($this->insurance_provider) && !empty($this->insurance_policy_number);
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'phone_mobile',
                'address_line_1',
                'city',
                'state',
                'postal_code',
                'emergency_contact_name',
                'emergency_contact_phone',
                'insurance_provider',
                'preferred_pharmacy_id',
                'consent_to_text',
                'consent_to_email',
                'consent_to_marketing',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Profile {$eventName} for user {$this->user->email}");
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