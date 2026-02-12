<?php

namespace Plugins\TrustpilotReview\EventSubscriber;

use App\Core\Event\Server\ServerPurchaseCompletedEvent;
use App\Core\Service\Plugin\PluginSettingService;
use Plugins\TrustpilotReview\Service\TrustpilotService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ServerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
        private readonly TrustpilotService $trustpilotService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ServerPurchaseCompletedEvent::class => 'onServerPurchaseCompleted',
        ];
    }

    public function onServerPurchaseCompleted(ServerPurchaseCompletedEvent $event): void
    {
        try {
            $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
            if (!$enabled) {
                return;
            }

            $afsEnabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'afs_enabled', false);
            if (!$afsEnabled) {
                $this->logger->debug('Trustpilot: AFS disabled, skipping invitation');
                return;
            }

            if (!$this->trustpilotService->isAfsConfigured()) {
                $this->logger->warning('Trustpilot: AFS is enabled but API is not fully configured');
                return;
            }

            $server = $event->getServer();
            $user = $server->getUser();

            if ($user === null) {
                $this->logger->warning('Trustpilot: Could not get user from server purchase event', [
                    'server_id' => $server->getId(),
                ]);
                return;
            }

            $userEmail = $user->getEmail();
            $userName = method_exists($user, 'getDisplayName')
                ? $user->getDisplayName()
                : (method_exists($user, 'getName') ? $user->getName() : $user->getEmail());

            if (empty($userEmail)) {
                $this->logger->warning('Trustpilot: User has no email address', [
                    'user_id' => $user->getId(),
                ]);
                return;
            }

            $this->trustpilotService->scheduleInvitation(
                $user->getId(),
                $userEmail,
                $userName,
                $server->getId()
            );

            $this->logger->info('Trustpilot: Review invitation scheduled for purchase', [
                'server_id' => $server->getId(),
                'user_id' => $user->getId(),
            ]);
        } catch (\Throwable $e) {
            // Never let Trustpilot failures break the purchase flow
            $this->logger->error('Trustpilot: Error handling purchase event', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
