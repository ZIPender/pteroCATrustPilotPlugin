<?php

namespace Plugins\TrustpilotReview\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrustpilotService
{
    /**
     * Check if user should see review popup for a server
     *
     * @param int $userId
     * @param int $serverId
     * @return array
     */
    public function shouldShowPopup($userId, $serverId)
    {
        // Check if plugin is enabled
        if (!config('trustpilot.enabled')) {
            return ['show' => false];
        }

        // Check if user has dismissed for this server
        $dismissed = DB::table('trustpilot_dismissals')
            ->where('user_id', $userId)
            ->where('server_id', $serverId)
            ->exists();

        if ($dismissed) {
            return ['show' => false];
        }

        // Get server expiry date
        $server = DB::table('servers')
            ->where('id', $serverId)
            ->where('user_id', $userId)
            ->first();

        if (!$server || !isset($server->expires_at)) {
            return ['show' => false];
        }

        $expiresAt = Carbon::parse($server->expires_at);
        $daysUntilExpiry = now()->diffInDays($expiresAt, false);
        $daysBeforeExpiry = config('trustpilot.days_before_expiry', 7);

        // Show popup if server expires within configured days
        if ($daysUntilExpiry <= $daysBeforeExpiry && $daysUntilExpiry >= 0) {
            return [
                'show' => true,
                'days_until_expiry' => (int) $daysUntilExpiry,
                'review_url' => config('trustpilot.review_url'),
            ];
        }

        return ['show' => false];
    }

    /**
     * Mark popup as dismissed for user and server
     *
     * @param int $userId
     * @param int $serverId
     * @return bool
     */
    public function dismissPopup($userId, $serverId)
    {
        return DB::table('trustpilot_dismissals')->insert([
            'user_id' => $userId,
            'server_id' => $serverId,
            'dismissed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    public function getSettings()
    {
        return [
            'days_before_expiry' => config('trustpilot.days_before_expiry'),
            'review_url' => config('trustpilot.review_url'),
            'enabled' => config('trustpilot.enabled'),
        ];
    }

    /**
     * Update plugin settings
     *
     * @param array $settings
     * @return bool
     */
    public function updateSettings($settings)
    {
        // In a real implementation, this would update the settings
        // in the database or config file. For now, we'll just return true.
        // This would typically update a settings table or environment config.
        return true;
    }
}
