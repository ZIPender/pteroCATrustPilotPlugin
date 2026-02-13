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

        // Detect current panel locale and pick matching text
        $locale = $this->translator->getLocale();
        $lang = str_starts_with($locale, 'fr') ? 'fr' : 'en';

        $domain = 'plugin_trustpilot_review';
        $t = fn(string $key, string $fallback): string => $this->translate($key, $domain, $fallback);

        return [
            'review_url' => $settings['review_url'],
            'popup_title' => $settings['popup_title_' . $lang],
            'popup_message' => $settings['popup_message_' . $lang],
            'plugin_name' => 'trustpilot-review',
            'test_mode' => $settings['test_mode'],
            'show_leave_review_button' => $settings['show_leave_review_button'],
            'business_unit_id' => $settings['business_unit_id'],
            'business_domain' => $settings['business_domain'],
            'trustbox_template_id' => $settings['trustbox_template_id'],
            'trustbox_theme' => $settings['trustbox_theme'],
            'trustbox_height' => $settings['trustbox_height'],
            'trustbox_stars' => $settings['trustbox_stars'],
            'trustbox_locale' => $settings['trustbox_locale'],
            'label_powered_by' => $t('plugin_trustpilot_review.widget.powered_by', 'Powered by Trustpilot'),
            'label_leave_review' => $t('plugin_trustpilot_review.widget.leave_review', 'Leave a Review'),
        ];
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

        return (bool) $this->pluginSettingService->get('trustpilot-review', 'enable_widget', true);
    }

    public function getColumnSize(): int
    {
        return 12;
    }

    private function translate(string $key, string $domain, string $fallback): string
    {
        $translated = $this->translator->trans($key, [], $domain);
        return ($translated === $key) ? $fallback : $translated;
    }
}
