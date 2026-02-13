<?php

namespace Plugins\TrustpilotReview\Widget;

use App\Core\Contract\Widget\WidgetInterface;
use App\Core\Enum\WidgetContext;
use App\Core\Enum\WidgetPosition;
use App\Core\Service\Plugin\PluginSettingService;
use Plugins\TrustpilotReview\Service\TrustpilotService;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrustpilotWidget implements WidgetInterface
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly TrustpilotService $trustpilotService,
        private readonly TranslatorInterface $translator,
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
        return 100;
    }

    public function getTemplate(): string
    {
        return '@PluginTrustpilotReview/widgets/trustpilot.html.twig';
    }

    public function getData(WidgetContext $context, array $contextData): array
    {
        $settings = $this->trustpilotService->getSettings();
        $trustpilotData = $this->trustpilotService->getTrustpilotData();

        // Translate UI strings using Symfony translator (picks up panel locale automatically)
        $domain = 'plugin_trustpilot_review';
        $t = fn(string $key, string $fallback): string => $this->translate($key, $domain, $fallback);

        $data = [
            'review_url' => $settings['review_url'],
            'popup_title' => $settings['popup_title'],
            'popup_message' => $settings['popup_message'],
            'plugin_name' => 'trustpilot-review',
            'test_mode' => $settings['test_mode'],
            'display_mode' => $settings['display_mode'],
            'show_leave_review_button' => $settings['show_leave_review_button'],
            'trustpilot_score' => $trustpilotData['score'],
            'trustpilot_stars' => $trustpilotData['stars'],
            'trustpilot_reviews_count' => $trustpilotData['reviews_count'],
            // Pre-translated UI strings
            'label_reviews' => $t('plugin_trustpilot_review.widget.reviews', 'reviews'),
            'label_no_reviews' => $t('plugin_trustpilot_review.widget.no_reviews', 'No reviews yet'),
            'label_powered_by' => $t('plugin_trustpilot_review.widget.powered_by', 'Powered by Trustpilot'),
            'label_leave_review' => $t('plugin_trustpilot_review.widget.leave_review', 'Leave a Review'),
        ];

        if ($settings['display_mode'] === 'trustbox') {
            $buId = $this->trustpilotService->resolveBusinessUnitId();
            $data['business_unit_id'] = $buId ?? '';
            $data['business_domain'] = $settings['business_domain'];
            $data['trustbox_template_id'] = $settings['trustbox_template_id'];
            $data['trustbox_theme'] = $settings['trustbox_theme'];
            $data['trustbox_height'] = $settings['trustbox_height'];
            $data['trustbox_stars'] = $settings['trustbox_stars'];
            $data['trustbox_locale'] = $this->pluginSettingService->get('trustpilot-review', 'afs_locale', 'en-US');
        } else {
            $data['reviews'] = $this->trustpilotService->fetchReviews();
        }

        return $data;
    }

    public function isVisible(WidgetContext $context, array $contextData): bool
    {
        if ($context !== WidgetContext::DASHBOARD) {
            return false;
        }

        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
        if (!$enabled) {
            return false;
        }

        $widgetEnabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enable_widget', true);
        if (!$widgetEnabled) {
            return false;
        }

        return true;
    }

    public function getColumnSize(): int
    {
        return 12;
    }

    private function translate(string $key, string $domain, string $fallback): string
    {
        $translated = $this->translator->trans($key, [], $domain);
        // If translator returns the key unchanged, it means no translation was found
        return ($translated === $key) ? $fallback : $translated;
    }
}
