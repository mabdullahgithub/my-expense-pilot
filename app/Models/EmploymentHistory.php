<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentHistory extends Model
{
    use HasFactory;

    protected $table = 'employment_history';

    protected $fillable = [
        'user_id',
        'profile_type',
        'company_name',
        'position',
        'employment_type',
        'salary',
        'salary_frequency',
        'start_date',
        'end_date',
        'description',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
        'is_current' => 'boolean',
    ];

    /**
     * Get the user that owns the employment history.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the incomes associated with this employment.
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Get the profile type label.
     */
    public function getProfileTypeLabel(): string
    {
        return match($this->profile_type) {
            'student' => 'Student',
            'employee' => 'Employee',
            'business_owner' => 'Business Owner',
            'freelancer' => 'Freelancer',
            default => 'Not Specified'
        };
    }

    /**
     * Get the employment type label.
     */
    public function getEmploymentTypeLabel(): string
    {
        return match($this->employment_type) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'freelance' => 'Freelance',
            default => 'Unknown'
        };
    }

    /**
     * Get the salary frequency label.
     */
    public function getSalaryFrequencyLabel(): string
    {
        return match($this->salary_frequency) {
            'hourly' => 'Per Hour',
            'monthly' => 'Per Month',
            'yearly' => 'Per Year',
            default => 'Not Specified'
        };
    }

    /**
     * Get the duration of employment.
     */
    public function getDuration(): string
    {
        $endDate = $this->end_date ?? now();
        $duration = $this->start_date->diffInMonths($endDate);
        
        if ($duration < 1) {
            return 'Less than 1 month';
        } elseif ($duration < 12) {
            return $duration . ' month' . ($duration > 1 ? 's' : '');
        } else {
            $years = floor($duration / 12);
            $remainingMonths = $duration % 12;
            
            $result = $years . ' year' . ($years > 1 ? 's' : '');
            if ($remainingMonths > 0) {
                $result .= ' and ' . $remainingMonths . ' month' . ($remainingMonths > 1 ? 's' : '');
            }
            
            return $result;
        }
    }

    /**
     * Scope to get current employments.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope to get past employments.
     */
    public function scopePast($query)
    {
        return $query->where('is_current', false);
    }
}