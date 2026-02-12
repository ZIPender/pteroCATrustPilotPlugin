<?php

namespace Plugins\TrustpilotReview;

use App\Core\Service\Plugin\PluginSettingService;
use Psr\Log\LoggerInterface;

class Bootstrap
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
    ) {}

    public function initialize(): void
    {
        $this->logger->info('Trustpilot Review plugin: Bootstrap initialization started');

        try {
            $this->verifyConfiguration();
            $this->logger->info('Trustpilot Review plugin: Bootstrap initialization completed');
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot Review plugin: Bootstrap initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function verifyConfiguration(): void
    {
        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);

        if (!$enabled) {
            $this->logger->info('Trustpilot Review plugin: Plugin is disabled');
            return;
        }

        // General settings
        $reviewUrl = $this->pluginSettingService->get('trustpilot-review', 'review_url', '');
        if (empty($reviewUrl) || $reviewUrl === 'https://www.trustpilot.com/evaluate/your-business') {
            $this->logger->warning('Trustpilot: Review URL not configured. Please set your Trustpilot business URL.');
        }

        // API credentials
        $apiKey = $this->pluginSettingService->get('trustpilot-review', 'api_key', '');
        $apiSecret = $this->pluginSettingService->get('trustpilot-review', 'api_secret', '');
        $businessUnitId = $this->pluginSettingService->get('trustpilot-review', 'business_unit_id', '');
        $businessDomain = $this->pluginSettingService->get('trustpilot-review', 'business_domain', '');

        if (empty($apiKey)) {
            $this->logger->warning('Trustpilot: API key not configured. Widget data and AFS invitations will not work.');
        } elseif (empty($apiSecret)) {
            $this->logger->warning('Trustpilot: API secret not configured. AFS invitations will not work.');
        }

        if (empty($businessUnitId) && empty($businessDomain)) {
            $this->logger->warning('Trustpilot: Neither Business Unit ID nor Business Domain is configured.');
        }

        // AFS settings
        $afsEnabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'afs_enabled', false);
        if ($afsEnabled) {
            if (empty($apiKey) || empty($apiSecret)) {
                $this->logger->warning('Trustpilot: AFS is enabled but API credentials are missing.');
            }

            $senderEmail = $this->pluginSettingService->get('trustpilot-review', 'afs_sender_email', '');
            $senderName = $this->pluginSettingService->get('trustpilot-review', 'afs_sender_name', '');
            if (empty($senderEmail) || empty($senderName)) {
                $this->logger->warning('Trustpilot: AFS sender email or name not configured. Invitations may fail.');
            }
        }

        // Display mode
        $displayMode = $this->pluginSettingService->get('trustpilot-review', 'display_mode', 'custom');
        if ($displayMode === 'trustbox' && empty($businessUnitId) && empty($businessDomain)) {
            $this->logger->warning('Trustpilot: TrustBox mode requires a Business Unit ID or Business Domain.');
        }
        if ($displayMode === 'custom' && empty($apiKey)) {
            $this->logger->warning('Trustpilot: Custom carousel mode requires an API key to fetch reviews.');
        }

        $this->logger->info('Trustpilot Review plugin: Configuration verified', [
            'display_mode' => $displayMode,
            'afs_enabled' => $afsEnabled,
            'api_configured' => !empty($apiKey),
        ]);
    }

    public function cleanup(): void
    {
        $this->logger->info('Trustpilot Review plugin: Cleanup completed (settings preserved)');
    }
}
