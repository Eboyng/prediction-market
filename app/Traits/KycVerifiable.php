<?php

namespace App\Traits;

/**
 * KycVerifiable trait for handling KYC status checks and validation
 * Used by User model
 */
trait KycVerifiable
{
    /**
     * Check if user's KYC is approved
     *
     * @return bool
     */
    public function isKycApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    /**
     * Check if user's KYC is pending
     *
     * @return bool
     */
    public function isKycPending(): bool
    {
        return $this->kyc_status === 'pending';
    }

    /**
     * Check if user's KYC is rejected
     *
     * @return bool
     */
    public function isKycRejected(): bool
    {
        return $this->kyc_status === 'rejected';
    }

    /**
     * Approve user's KYC
     *
     * @return bool
     */
    public function approveKyc(): bool
    {
        $updated = $this->update(['kyc_status' => 'approved']);
        
        if ($updated) {
            $this->logActivity('kyc_approved', [
                'previous_status' => $this->getOriginal('kyc_status'),
                'approved_at' => now(),
            ]);
        }
        
        return $updated;
    }

    /**
     * Reject user's KYC
     *
     * @param string|null $reason
     * @return bool
     */
    public function rejectKyc(?string $reason = null): bool
    {
        $updated = $this->update(['kyc_status' => 'rejected']);
        
        if ($updated) {
            $this->logActivity('kyc_rejected', [
                'previous_status' => $this->getOriginal('kyc_status'),
                'reason' => $reason,
                'rejected_at' => now(),
            ]);
        }
        
        return $updated;
    }

    /**
     * Reset KYC status to pending
     *
     * @return bool
     */
    public function resetKyc(): bool
    {
        $updated = $this->update(['kyc_status' => 'pending']);
        
        if ($updated) {
            $this->logActivity('kyc_reset', [
                'previous_status' => $this->getOriginal('kyc_status'),
                'reset_at' => now(),
            ]);
        }
        
        return $updated;
    }

    /**
     * Check if user can perform KYC-restricted actions
     *
     * @return bool
     */
    public function canPerformKycRestrictedActions(): bool
    {
        return $this->isKycApproved();
    }

    /**
     * Validate KYC requirements for withdrawal
     *
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
    public function validateKycForWithdrawal(): void
    {
        if (!$this->isKycApproved()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'kyc' => 'You must complete KYC verification before withdrawing funds.',
            ]);
        }
    }

    /**
     * Get KYC status display name
     *
     * @return string
     */
    public function getKycStatusDisplayName(): string
    {
        return match ($this->kyc_status) {
            'pending' => 'Pending Verification',
            'approved' => 'Verified',
            'rejected' => 'Verification Failed',
            default => 'Unknown Status',
        };
    }

    /**
     * Get KYC status color for UI
     *
     * @return string
     */
    public function getKycStatusColor(): string
    {
        return match ($this->kyc_status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
