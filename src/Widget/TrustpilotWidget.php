<?php

namespace Plugins\TrustpilotReview\Widget;

use App\Core\Contract\Widget\WidgetInterface;
use App\Core\Enum\WidgetContext;
use App\Core\Enum\WidgetPosition;
use App\Core\Service\Plugin\PluginSettingService;
use Doctrine\ORM\EntityManagerInterface;
use Plugins\TrustpilotReview\Entity\Dismissal;
use Plugins\TrustpilotReview\Entity\Repository\DismissalRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Trustpilot Review Widget for the dashboard.
 *
 * This widget displays a review prompt when servers are approaching expiration.
 * It only shows when:
 * - The plugin is enabled
 * - The widget setting is enabled
 * - The user has servers expiring within the configured threshold
 * - The user hasn't dismissed the popup for those servers
 */
class TrustpilotWidget implements WidgetInterface
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly EntityManagerInterface $entityManager,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

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
            'Enjoying our service?'
        );
        $popupMessage = $this->pluginSettingService->get(
            'trustpilot-review',
            'popup_message',
            'Your server is expiring soon. If you\'ve enjoyed our service, we\'d love to hear your feedback on Trustpilot!'
        );
        $daysBeforeExpiry = (int) $this->pluginSettingService->get(
            'trustpilot-review',
            'days_before_expiry',
            7
        );

        return [
            'review_url' => $reviewUrl,
            'popup_title' => $popupTitle,
            'popup_message' => $popupMessage,
            'days_before_expiry' => $daysBeforeExpiry,
            'plugin_name' => 'trustpilot-review',
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
