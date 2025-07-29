<?php

namespace App\Traits;

use App\Models\ActivityLog;

/**
 * Auditable trait for logging user actions to ActivityLog
 * Used by User, Wallet, Stake, Referral models
 */
trait Auditable
{
    /**
     * Log an action to the activity log
     *
     * @param string $action
     * @param array|null $metadata
     * @param int|null $userId
     * @return ActivityLog
     */
    public function logActivity(string $action, ?array $metadata = null, ?int $userId = null): ActivityLog
    {
        // Determine user ID, fallback to admin user (ID 1) during seeding
        $userId = $userId ?? ($this->user_id ?? auth()->id());
        
        // If no user ID is available (during seeding), use admin user (ID 1)
        if (!$userId) {
            $userId = 1; // Admin user
        }
        
        // Verify the user exists before creating activity log
        if (!\App\Models\User::find($userId)) {
            // Skip logging if user doesn't exist (during seeding)
            return new ActivityLog();
        }
        
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'metadata' => $metadata ? json_encode($metadata) : null,
        ]);
    }

    /**
     * Boot the auditable trait
     */
    protected static function bootAuditable(): void
    {
        // Skip auditing during database seeding
        if (app()->runningInConsole() && app()->environment() !== 'testing') {
            return;
        }

        // Log model creation
        static::created(function ($model) {
            $model->logActivity('created', [
                'model' => class_basename($model),
                'id' => $model->id,
                'attributes' => $model->getAttributes(),
            ]);
        });

        // Log model updates
        static::updated(function ($model) {
            $model->logActivity('updated', [
                'model' => class_basename($model),
                'id' => $model->id,
                'changes' => $model->getChanges(),
                'original' => $model->getOriginal(),
            ]);
        });

        // Log model deletion
        static::deleted(function ($model) {
            $model->logActivity('deleted', [
                'model' => class_basename($model),
                'id' => $model->id,
                'attributes' => $model->getAttributes(),
            ]);
        });
    }

    /**
     * Get activity logs for this model
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
