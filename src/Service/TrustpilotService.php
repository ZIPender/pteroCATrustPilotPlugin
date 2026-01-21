<?php

namespace Plugins\TrustpilotReview\Service;

use App\Core\Service\Plugin\PluginSettingService;
use Doctrine\ORM\EntityManagerInterface;
use Plugins\TrustpilotReview\Entity\Dismissal;
use Plugins\TrustpilotReview\Entity\Repository\DismissalRepository;
use Psr\Log\LoggerInterface;

/**
 * Service for Trustpilot Review plugin business logic.
 *
 * Handles the core functionality of checking if popups should be shown
 * and recording dismissals.
 */
class TrustpilotService
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Check if the review popup should be shown for a user and server.
     *
     * @param int $userId The user ID
     * @param int $serverId The server ID
     * @param \DateTimeInterface|null $serverExpiresAt The server expiration date
     * @return array Result with 'show' boolean and optional 'review_url', 'days_until_expiry'
     */
    public function shouldShowPopup(int $userId, int $serverId, ?\DateTimeInterface $serverExpiresAt = null): array
    {
        // Check if plugin is enabled
        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
        if (!$enabled) {
            return ['show' => false, 'reason' => 'plugin_disabled'];
        }

        // Check if widget is enabled
        $widgetEnabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enable_widget', true);
        if (!$widgetEnabled) {
            return ['show' => false, 'reason' => 'widget_disabled'];
        }

        // Check if already dismissed
        /** @var DismissalRepository $repository */
        $repository = $this->entityManager->getRepository(Dismissal::class);
        if ($repository->isDismissed($userId, $serverId)) {
            return ['show' => false, 'reason' => 'dismissed'];
        }

        // Check server expiration
        if ($serverExpiresAt === null) {
            return ['show' => false, 'reason' => 'no_expiry'];
        }

        $daysBeforeExpiry = (int) $this->pluginSettingService->get('trustpilot-review', 'days_before_expiry', 7);
        $now = new \DateTimeImmutable();
        $daysUntilExpiry = $now->diff($serverExpiresAt)->days;

        // Check if server expires after now (not already expired)
        if ($serverExpiresAt < $now) {
            return ['show' => false, 'reason' => 'already_expired'];
        }

        // Check if within the days threshold
        if ($daysUntilExpiry <= $daysBeforeExpiry) {
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

            return [
                'show' => true,
                'days_until_expiry' => $daysUntilExpiry,
                'review_url' => $reviewUrl,
                'popup_title' => $popupTitle,
                'popup_message' => $popupMessage,
            ];
        }

        return ['show' => false, 'reason' => 'not_within_threshold'];
    }

    /**
     * Record a dismissal for a user and server.
     *
     * @param int $userId The user ID
     * @param int $serverId The server ID
     * @return bool True if dismissal was recorded, false if already existed
     */
    public function dismiss(int $userId, int $serverId): bool
    {
        /** @var DismissalRepository $repository */
        $repository = $this->entityManager->getRepository(Dismissal::class);

        // Check if already dismissed
        if ($repository->isDismissed($userId, $serverId)) {
            return false;
        }

        try {
            $dismissal = new Dismissal();
            $dismissal->setUserId($userId);
            $dismissal->setServerId($serverId);

            $this->entityManager->persist($dismissal);
            $this->entityManager->flush();

            $this->logger->info('Trustpilot Review: Popup dismissed', [
                'user_id' => $userId,
                'server_id' => $serverId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot Review: Failed to record dismissal', [
                'user_id' => $userId,
                'server_id' => $serverId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get statistics about dismissals.
     *
     * @return array Statistics including total_dismissals and unique_users
     */
    public function getStats(): array
    {
        /** @var DismissalRepository $repository */
        $repository = $this->entityManager->getRepository(Dismissal::class);

        return [
            'total_dismissals' => $repository->countTotal(),
            'unique_users' => $repository->countUniqueUsers(),
        ];
    }

    /**
     * Get plugin settings.
     *
     * @return array Current plugin settings
     */
    public function getSettings(): array
    {
        return [
            'enabled' => (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true),
            'days_before_expiry' => (int) $this->pluginSettingService->get('trustpilot-review', 'days_before_expiry', 7),
            'review_url' => $this->pluginSettingService->get('trustpilot-review', 'review_url', ''),
            'enable_widget' => (bool) $this->pluginSettingService->get('trustpilot-review', 'enable_widget', true),
            'popup_title' => $this->pluginSettingService->get('trustpilot-review', 'popup_title', 'Enjoying our service?'),
            'popup_message' => $this->pluginSettingService->get('trustpilot-review', 'popup_message', ''),
        ];
    }
}
