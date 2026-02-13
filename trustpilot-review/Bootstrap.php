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
        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);

        if (!$enabled) {
            $this->logger->info('Trustpilot Review plugin: Plugin is disabled');
            return;
        }

        $buId = $this->pluginSettingService->get('trustpilot-review', 'business_unit_id', '');
        if (empty($buId)) {
            $this->logger->warning('Trustpilot: Business Unit ID not configured. The TrustBox widget will not display.');
        }

        $reviewUrl = $this->pluginSettingService->get('trustpilot-review', 'review_url', '');
        if (empty($reviewUrl) || $reviewUrl === 'https://www.trustpilot.com/evaluate/your-business') {
            $this->logger->warning('Trustpilot: Review URL not configured. Please set your Trustpilot business URL.');
        }

        $this->logger->info('Trustpilot Review plugin: Initialized');
    }

    public function cleanup(): void
    {
        $this->logger->info('Trustpilot Review plugin: Cleanup completed');
    }
}
