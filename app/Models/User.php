<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use App\Models\EmploymentHistory;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'birthday',
        'website',
        'about',
        'country',
        'currency',
        'profile_type',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'profile_type' => 'array',
    ];

    /**
     * Get the URL to the user's profile photo.
     *
     * @param string $value
     * @return string|null
     */
    public function getAvatarAttribute(?string $value): string
    {
        return $value
            ? Storage::disk('public')->url($value)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the categories for the user.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the incomes for the user.
     *
     * @return HasMany
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Get the expenses for the user.
     *
     * @return HasMany
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the employment history for the user.
     *
     * @return HasMany
     */
    public function employmentHistory(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    /**
     * Get the current employment for the user.
     *
     * @return HasMany
     */
    public function currentEmployment(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class)->where('is_current', true);
    }

    /**
     * Get the user's profile type label (first one if multiple).
     *
     * @return string
     */
    public function getProfileTypeLabel(): string
    {
        if (!is_array($this->profile_type) || empty($this->profile_type)) {
            return 'Not Specified';
        }

        $firstType = $this->profile_type[0];
        return match($firstType) {
            'student' => 'Student',
            'employee' => 'Employee',
            'business_owner' => 'Business Owner',
            'freelancer' => 'Freelancer',
            default => 'Not Specified'
        };
    }

    /**
     * Get the user's profile type labels.
     *
     * @return array
     */
    public function getProfileTypeLabels(): array
    {
        if (!is_array($this->profile_type)) {
            return ['Not Specified'];
        }

        return array_map(function($type) {
            return match($type) {
                'student' => 'Student',
                'employee' => 'Employee',
                'business_owner' => 'Business Owner',
                'freelancer' => 'Freelancer',
                default => 'Not Specified'
            };
        }, $this->profile_type);
    }

    /**
     * Get available profile type options.
     *
     * @return array
     */
    public static function getProfileTypeOptions(): array
    {
        return [
            'student' => 'Student',
            'employee' => 'Employee',
            'business_owner' => 'Business Owner',
            'freelancer' => 'Freelancer',
        ];
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
        return true;
    }
}
