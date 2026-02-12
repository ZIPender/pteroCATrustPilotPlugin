<?php

namespace Plugins\TrustpilotReview;

use App\Core\Service\Plugin\PluginSettingService;
use Psr\Log\LoggerInterface;

/**
 * Bootstrap class for Trustpilot Review plugin initialization.
 *
 * The Bootstrap class is invoked when the plugin is enabled and provides
 * a centralized place for:
 * - Initializing default settings
 * - Registering custom services
 * - Setting up event listeners programmatically
 * - Performing one-time setup tasks
 * - Logging plugin startup
 */
class Bootstrap
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Initialize the plugin.
     *
     * This method is called once when the plugin is enabled.
     */
    public function initialize(): void
    {
        $this->logger->info('Trustpilot Review plugin: Bootstrap initialization started');

        try {
            // Verify configuration
            $this->verifyConfiguration();

            $this->logger->info('Trustpilot Review plugin: Bootstrap initialization completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot Review plugin: Bootstrap initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify plugin configuration.
     */
    private function verifyConfiguration(): void
    {
        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
        $reviewUrl = $this->pluginSettingService->get('trustpilot-review', 'review_url', '');
        $daysBeforeExpiry = (int) $this->pluginSettingService->get('trustpilot-review', 'days_before_expiry', 7);

        if ($enabled) {
            if (empty($reviewUrl) || $reviewUrl === 'https://www.trustpilot.com/evaluate/your-business') {
                $this->logger->warning('Trustpilot Review plugin: Review URL not configured. Please set your Trustpilot business URL in plugin settings.');
            } else {
                $this->logger->info('Trustpilot Review plugin: Configured', [
                    'review_url' => $reviewUrl,
                    'days_before_expiry' => $daysBeforeExpiry,
                ]);
            }
        } else {
            $this->logger->info('Trustpilot Review plugin: Plugin is disabled');
        }
    }

    /**
     * Cleanup method called when plugin is disabled.
     */
    public function cleanup(): void
    {
        $this->logger->info('Trustpilot Review plugin: Bootstrap cleanup started');

        // No cleanup needed - settings are preserved in case plugin is re-enabled

        $this->logger->info('Trustpilot Review plugin: Bootstrap cleanup completed');
    }
}
