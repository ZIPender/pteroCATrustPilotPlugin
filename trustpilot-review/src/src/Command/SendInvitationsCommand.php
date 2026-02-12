<?php

namespace Plugins\TrustpilotReview\Command;

use App\Core\Service\Plugin\PluginSettingService;
use Plugins\TrustpilotReview\Service\TrustpilotService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'trustpilot:send-invitations',
    description: 'Process and send pending Trustpilot review invitations',
)]
class SendInvitationsCommand extends Command
{
    public function __construct(
        private readonly TrustpilotService $trustpilotService,
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $enabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'enabled', true);
        if (!$enabled) {
            $io->info('Trustpilot plugin is disabled.');
            return Command::SUCCESS;
        }

        $afsEnabled = (bool) $this->pluginSettingService->get('trustpilot-review', 'afs_enabled', false);
        if (!$afsEnabled) {
            $io->info('AFS invitations are disabled.');
            return Command::SUCCESS;
        }

        if (!$this->trustpilotService->isAfsConfigured()) {
            $io->warning('AFS is enabled but the API is not fully configured.');
            return Command::FAILURE;
        }

        try {
            $sentCount = $this->trustpilotService->processPendingInvitations();

            if ($sentCount > 0) {
                $io->success(sprintf('%d invitation(s) sent successfully.', $sentCount));
            } else {
                $io->info('No pending invitations to process.');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->logger->error('Trustpilot: Command failed', [
                'error' => $e->getMessage(),
            ]);
            $io->error('Failed to process invitations: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
