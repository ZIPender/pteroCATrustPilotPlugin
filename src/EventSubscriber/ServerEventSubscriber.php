<?php

namespace Plugins\TrustpilotReview\EventSubscriber;

use App\Core\Event\Server\ServerPurchaseCompletedEvent;
use App\Core\Service\Plugin\PluginSettingService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Server Event Subscriber for Trustpilot Review plugin.
 *
 * Listens to server-related events for potential future enhancements
 * like tracking when servers are purchased or renewed.
 */
class ServerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Returns the events this subscriber listens to.
     *
     * @return array<string, string|array>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ServerPurchaseCompletedEvent::class => 'onServerPurchaseCompleted',
        ];
    }

    /**
     * Handle server purchase completed event.
     *
     * This could be used to track purchase patterns or
     * schedule review prompts for future dates.
     */
    public function onServerPurchaseCompleted(ServerPurchaseCompletedEvent $event): void
    {
        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
        if (!$enabled) {
            return;
        }

        // Log the purchase for potential analytics
        $server = $event->getServer();
        $this->logger->debug('Trustpilot Review: Server purchase completed', [
            'server_id' => $server->getId(),
        ]);
    }
}
