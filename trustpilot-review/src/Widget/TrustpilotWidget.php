<?php

namespace Plugins\TrustpilotReview\Widget;

use App\Core\Contract\Widget\WidgetInterface;
use App\Core\Enum\WidgetContext;
use App\Core\Enum\WidgetPosition;
use App\Core\Service\Plugin\PluginSettingService;
use Plugins\TrustpilotReview\Service\TrustpilotService;

/**
 * Trustpilot Review Widget for the dashboard.
 *
 * This widget displays a permanent card showing Trustpilot rating
 * and prompting users to leave a review.
 */
class TrustpilotWidget implements WidgetInterface
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly TrustpilotService $trustpilotService,
    ) {
    }

    public function getName(): string
    {
        return 'trustpilot_review';
    }

    public function getDisplayName(): string
    {
        return 'Trustpilot Review';
    }

    public function getSupportedContexts(): array
    {
        return [WidgetContext::DASHBOARD];
    }

    public function getPosition(): WidgetPosition
    {
        return WidgetPosition::RIGHT;
    }

    public function getPriority(): int
    {
        return 100; // High priority to show prominently
    }

    public function getTemplate(): string
    {
        return '@PluginTrustpilotReview/widgets/trustpilot.html.twig';
    }

    public function getData(WidgetContext $context, array $contextData): array
    {
        $reviewUrl = $this->pluginSettingService->get(
            'trustpilot-review',
            'review_url',
            'https://www.trustpilot.com/evaluate/your-business'
        );
        $popupTitle = $this->pluginSettingService->get(
            'trustpilot-review',
            'popup_title',
            'Vous appréciez notre service ?'
        );
        $popupMessage = $this->pluginSettingService->get(
            'trustpilot-review',
            'popup_message',
            'Si vous avez apprécié notre service, nous serions ravis d\'avoir votre avis sur Trustpilot !'
        );

        // Fetch Trustpilot data
        $trustpilotData = $this->trustpilotService->getTrustpilotData($reviewUrl);

        return [
            'review_url' => $reviewUrl,
            'popup_title' => $popupTitle,
            'popup_message' => $popupMessage,
            'plugin_name' => 'trustpilot-review',
            'test_mode' => (bool) $this->pluginSettingService->get('trustpilot-review', 'test_mode', false),
            'trustpilot_score' => $trustpilotData['score'],
            'trustpilot_reviews_count' => $trustpilotData['reviews_count'],
        ];
    }

    public function isVisible(WidgetContext $context, array $contextData): bool
    {
        // Only show on dashboard context
        if ($context !== WidgetContext::DASHBOARD) {
            return false;
        }

        // Check if plugin is enabled
        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
        if (!$enabled) {
            return false;
        }

        // Check if widget is enabled
        $widgetEnabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enable_widget', true);
        if (!$widgetEnabled) {
            return false;
        }

        return true;
    }

    public function getColumnSize(): int
    {
        return 12; // Full width within RIGHT position
    }
}
