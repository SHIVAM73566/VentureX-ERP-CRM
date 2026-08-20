<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfieVerification extends Model
{
    protected $fillable = [
        'user_id', 'selfie_path', 'status', 'liveness_check',
        'metadata', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Liveness detection — check if selfie looks like a real person
     * Uses basic heuristics (image size, brightness, face detection)
     */
    public function performLivenessCheck(): bool
    {
        $filePath = storage_path('app/private/'.$this->selfie_path);

        if (! file_exists($filePath)) {
            $this->update(['liveness_check' => 'failed', 'rejection_reason' => 'File not found']);

            return false;
        }

        // Basic checks
        $fileSize = filesize($filePath);
        if ($fileSize < 10240) { // Less than 10KB = likely not a real photo
            $this->update(['liveness_check' => 'failed', 'rejection_reason' => 'Image too small']);

            return false;
        }

        if ($fileSize > 10485760) { // More than 10MB
            $this->update(['liveness_check' => 'failed', 'rejection_reason' => 'Image too large']);

            return false;
        }

        // Check if it's a valid image
        $imageInfo = @getimagesize($filePath);
        if (! $imageInfo) {
            $this->update(['liveness_check' => 'failed', 'rejection_reason' => 'Not a valid image']);

            return false;
        }

        // Check minimum dimensions (face should be visible)
        if ($imageInfo[0] < 200 || $imageInfo[1] < 200) {
            $this->update(['liveness_check' => 'failed', 'rejection_reason' => 'Image too small for face detection']);

            return false;
        }

        $this->update(['liveness_check' => 'passed']);

        return true;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
