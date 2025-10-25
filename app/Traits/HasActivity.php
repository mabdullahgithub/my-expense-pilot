<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

trait HasActivity
{
    /**
     * The "booted" method of the trait.
     *
     * @return void
     */
    public static function bootHasActivity(): void
    {
        static::created(function($model) {
            // Only create activity if we have a user_id (either from Auth or model)
            $userId = Auth::id() ?? $model->user_id ?? null;
            
            if ($userId) {
                $model->activity()->create([
                    'user_id' => $userId,
                ]);
            }
        });

        static::deleting(function($model) {
            if ($model->activity) {
                $model->activity->delete();
            }
        });
    }

    /**
     * Get the model's activity.
     *
     * @return MorphOne
     */
    public function activity(): MorphOne
    {
        return $this->morphOne(Activity::class, 'subject');
    }
}
