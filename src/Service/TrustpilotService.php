<?php

namespace Plugins\TrustpilotReview\Service;

use App\Core\Service\Plugin\PluginSettingService;

class TrustpilotService
{
    private const PLUGIN_ID = 'trustpilot-review';

    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
    ) {}

    public function getSettings(): array
    {
        return [
            'enabled' => (bool) $this->getSetting('enabled', true),
            'review_url' => $this->getSetting('review_url', ''),
            'test_mode' => (bool) $this->getSetting('test_mode', false),
            'business_unit_id' => $this->getSetting('business_unit_id', ''),
            'business_domain' => $this->getSetting('business_domain', ''),
            'enable_widget' => (bool) $this->getSetting('enable_widget', true),
            'popup_title_en' => $this->getSetting('popup_title_en', 'Enjoying our service?'),
            'popup_title_fr' => $this->getSetting('popup_title_fr', 'Vous appréciez notre service ?'),
            'popup_message_en' => $this->getSetting('popup_message_en', 'If you\'ve enjoyed our service, we\'d love to hear your feedback on Trustpilot!'),
            'popup_message_fr' => $this->getSetting('popup_message_fr', 'Si vous avez apprécié notre service, nous serions ravis d\'avoir votre avis sur Trustpilot !'),
            'show_leave_review_button' => (bool) $this->getSetting('show_leave_review_button', true),
            'trustbox_template_id' => $this->getSetting('trustbox_template_id', '53aa8912dec7e10d38f59f36'),
            'trustbox_theme' => $this->getSetting('trustbox_theme', 'light'),
            'trustbox_height' => $this->getSetting('trustbox_height', '140px'),
            'trustbox_stars' => $this->getSetting('trustbox_stars', '4,5'),
            'trustbox_locale' => $this->getSetting('trustbox_locale', 'en-US'),
        ];
    }

    private function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->pluginSettingService->get(self::PLUGIN_ID, $key, $default);
    }
}
