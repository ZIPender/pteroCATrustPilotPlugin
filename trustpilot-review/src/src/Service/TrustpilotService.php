<?php

namespace Plugins\TrustpilotReview\Service;

use App\Core\Service\Plugin\PluginSettingService;
use Doctrine\ORM\EntityManagerInterface;
use Plugins\TrustpilotReview\Entity\InvitationLog;
use Plugins\TrustpilotReview\Entity\Repository\InvitationLogRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrustpilotService
{
    private const PLUGIN_ID = 'trustpilot-review';
    private const CACHE_TTL = 3600;
    private const TOKEN_CACHE_KEY = 'trustpilot_oauth_token';
    private const BU_CACHE_TTL = 86400; // 24 hours

    private const API_BASE = 'https://api.trustpilot.com/v1';
    private const INVITATIONS_API_BASE = 'https://invitations-api.trustpilot.com/v1';
    private const TOKEN_ENDPOINT = self::API_BASE . '/oauth/oauth-business-users-for-applications/accesstoken';

    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly EntityManagerInterface $entityManager,
        private readonly InvitationLogRepository $invitationLogRepository,
    ) {}

    // ──────────────────────────────────────────────
    // OAuth Authentication
    // ──────────────────────────────────────────────

    public function getAccessToken(): string
    {
        return $this->cache->get(self::TOKEN_CACHE_KEY, function (ItemInterface $item) {
            $apiKey = $this->getSetting('api_key');
            $apiSecret = $this->getSetting('api_secret');

            if (empty($apiKey) || empty($apiSecret)) {
                throw new \RuntimeException('Trustpilot API key and secret are required for authentication');
            }

            $response = $this->httpClient->request('POST', self::TOKEN_ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => 'grant_type=client_credentials',
                'timeout' => 10,
            ]);

            $data = $response->toArray();

            if (empty($data['access_token'])) {
                throw new \RuntimeException('Trustpilot OAuth response missing access_token');
            }

            $expiresIn = (int) ($data['expires_in'] ?? 3600);
            $item->expiresAfter(max($expiresIn - 60, 60));

            $this->logger->info('Trustpilot: OAuth token acquired', [
                'expires_in' => $expiresIn,
            ]);

            return $data['access_token'];
        });
    }

    // ──────────────────────────────────────────────
    // Business Unit Resolution
    // ──────────────────────────────────────────────

    public function resolveBusinessUnitId(): ?string
    {
        $buId = $this->getSetting('business_unit_id');
        if (!empty($buId)) {
            return $buId;
        }

        $domain = $this->getSetting('business_domain');
        if (empty($domain)) {
            $this->logger->warning('Trustpilot: Neither business_unit_id nor business_domain is configured');
            return null;
        }

        $cacheKey = 'trustpilot_bu_id_' . md5($domain);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($domain) {
                $item->expiresAfter(self::BU_CACHE_TTL);

                $apiKey = $this->getSetting('api_key');
                if (empty($apiKey)) {
                    throw new \RuntimeException('API key required to resolve business unit ID');
                }

                $response = $this->httpClient->request('GET', self::API_BASE . '/business-units/find', [
                    'headers' => ['apikey' => $apiKey],
                    'query' => ['name' => $domain],
                    'timeout' => 10,
                ]);

                $data = $response->toArray();

                if (empty($data['id'])) {
                    throw new \RuntimeException('Could not find business unit for domain: ' . $domain);
                }

                $this->logger->info('Trustpilot: Resolved business unit ID', [
                    'domain' => $domain,
                    'business_unit_id' => $data['id'],
                ]);

                return $data['id'];
            });
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot: Failed to resolve business unit ID', [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ──────────────────────────────────────────────
    // Fetch Aggregate Data (score + review count)
    // ──────────────────────────────────────────────

    public function getTrustpilotData(): array
    {
        $buId = $this->resolveBusinessUnitId();
        if (empty($buId)) {
            return $this->getDefaultData();
        }

        $cacheKey = 'trustpilot_data_' . md5($buId);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($buId) {
                $item->expiresAfter(self::CACHE_TTL);

                $apiKey = $this->getSetting('api_key');
                if (empty($apiKey)) {
                    return $this->getDefaultData();
                }

                $response = $this->httpClient->request('GET', self::API_BASE . '/business-units/' . $buId, [
                    'headers' => ['apikey' => $apiKey],
                    'timeout' => 10,
                ]);

                $data = $response->toArray();

                $score = (float) ($data['score']['trustScore'] ?? 0);
                $reviewsCount = (int) ($data['numberOfReviews']['total'] ?? 0);
                $stars = (float) ($data['score']['stars'] ?? 0);

                $this->logger->info('Trustpilot: Fetched aggregate data', [
                    'score' => $score,
                    'stars' => $stars,
                    'reviews_count' => $reviewsCount,
                ]);

                return [
                    'score' => $score,
                    'stars' => $stars,
                    'reviews_count' => $reviewsCount,
                ];
            });
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot: Failed to fetch aggregate data', [
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultData();
        }
    }

    // ──────────────────────────────────────────────
    // Fetch Reviews (for custom carousel)
    // ──────────────────────────────────────────────

    public function fetchReviews(): array
    {
        $buId = $this->resolveBusinessUnitId();
        if (empty($buId)) {
            return [];
        }

        $apiKey = $this->getSetting('api_key');
        if (empty($apiKey)) {
            return [];
        }

        $perPage = (int) $this->getSetting('carousel_review_count', 5);
        $minStars = (int) $this->getSetting('carousel_min_stars', 4);

        // Build stars filter: all ratings from minStars to 5
        $starsFilter = implode(',', range($minStars, 5));

        $cacheKey = 'trustpilot_reviews_' . md5($buId . '_' . $perPage . '_' . $starsFilter);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($buId, $apiKey, $perPage, $starsFilter) {
                $item->expiresAfter(self::CACHE_TTL);

                $response = $this->httpClient->request('GET', self::API_BASE . '/business-units/' . $buId . '/reviews', [
                    'headers' => ['apikey' => $apiKey],
                    'query' => [
                        'orderBy' => 'createdat.desc',
                        'perPage' => $perPage,
                        'stars' => $starsFilter,
                    ],
                    'timeout' => 10,
                ]);

                $data = $response->toArray();
                $reviews = [];

                foreach ($data['reviews'] ?? [] as $review) {
                    $reviews[] = [
                        'id' => $review['id'] ?? '',
                        'title' => $review['title'] ?? '',
                        'text' => $review['text'] ?? '',
                        'stars' => (int) ($review['stars'] ?? 0),
                        'created_at' => $review['createdAt'] ?? '',
                        'consumer_name' => $review['consumer']['displayName'] ?? 'Anonymous',
                    ];
                }

                $this->logger->info('Trustpilot: Fetched reviews', [
                    'count' => count($reviews),
                ]);

                return $reviews;
            });
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot: Failed to fetch reviews', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    // ──────────────────────────────────────────────
    // AFS - Invitation Management
    // ──────────────────────────────────────────────

    public function scheduleInvitation(int $userId, string $userEmail, string $userName, int $serverId): ?InvitationLog
    {
        if ($this->invitationLogRepository->hasExistingInvitation($userId, $serverId)) {
            $this->logger->info('Trustpilot: Invitation already exists, skipping', [
                'user_id' => $userId,
                'server_id' => $serverId,
            ]);
            return $this->invitationLogRepository->findByUserAndServer($userId, $serverId);
        }

        $invitation = new InvitationLog();
        $invitation->setUserId($userId);
        $invitation->setUserEmail($userEmail);
        $invitation->setUserName($userName);
        $invitation->setServerId($serverId);
        $invitation->setReferenceNumber('SERVER-' . $serverId);

        $sendMode = $this->getSetting('afs_send_mode', 'immediate');

        if ($sendMode === 'delayed') {
            $delayHours = (int) $this->getSetting('afs_delay_hours', 72);
            $invitation->setScheduledAt(new \DateTimeImmutable('+' . $delayHours . ' hours'));
        }
        // For immediate mode, scheduledAt defaults to now (set in constructor)

        $this->entityManager->persist($invitation);
        $this->entityManager->flush();

        $this->logger->info('Trustpilot: Invitation scheduled', [
            'user_id' => $userId,
            'server_id' => $serverId,
            'send_mode' => $sendMode,
            'scheduled_at' => $invitation->getScheduledAt()->format('Y-m-d H:i:s'),
        ]);

        // If immediate, send right away
        if ($sendMode === 'immediate') {
            $this->sendInvitation($invitation);
        }

        return $invitation;
    }

    public function sendInvitation(InvitationLog $invitation): bool
    {
        $buId = $this->resolveBusinessUnitId();
        if (empty($buId)) {
            $invitation->setStatus(InvitationLog::STATUS_FAILED);
            $invitation->setErrorMessage('Business unit ID not configured');
            $this->entityManager->flush();
            return false;
        }

        try {
            $token = $this->getAccessToken();

            $body = [
                'consumerEmail' => $invitation->getUserEmail(),
                'consumerName' => $invitation->getUserName(),
                'referenceNumber' => $invitation->getReferenceNumber(),
                'locale' => $this->getSetting('afs_locale', 'en-US'),
                'type' => 'email',
                'serviceReviewInvitation' => [],
            ];

            $senderEmail = $this->getSetting('afs_sender_email');
            if (!empty($senderEmail)) {
                $body['senderEmail'] = $senderEmail;
            }

            $senderName = $this->getSetting('afs_sender_name');
            if (!empty($senderName)) {
                $body['senderName'] = $senderName;
            }

            $replyTo = $this->getSetting('afs_reply_to');
            if (!empty($replyTo)) {
                $body['replyTo'] = $replyTo;
            }

            $templateId = $this->getSetting('afs_template_id');
            if (!empty($templateId)) {
                $body['serviceReviewInvitation']['templateId'] = $templateId;
            }

            $redirectUri = $this->getSetting('afs_redirect_uri');
            if (!empty($redirectUri)) {
                $body['serviceReviewInvitation']['redirectUri'] = $redirectUri;
            }

            // Remove empty serviceReviewInvitation if no properties set
            if (empty($body['serviceReviewInvitation'])) {
                unset($body['serviceReviewInvitation']);
            }

            $response = $this->httpClient->request(
                'POST',
                self::INVITATIONS_API_BASE . '/private/business-units/' . $buId . '/email-invitations',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $body,
                    'timeout' => 15,
                ]
            );

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getContent(false);

            if ($statusCode >= 200 && $statusCode < 300) {
                $invitation->setStatus(InvitationLog::STATUS_SENT);
                $invitation->setSentAt(new \DateTimeImmutable());
                $invitation->setTrustpilotResponse($responseBody);
                $this->entityManager->flush();

                $this->logger->info('Trustpilot: Invitation sent successfully', [
                    'user_email' => $invitation->getUserEmail(),
                    'reference' => $invitation->getReferenceNumber(),
                ]);

                return true;
            }

            $invitation->setStatus(InvitationLog::STATUS_FAILED);
            $invitation->setErrorMessage('HTTP ' . $statusCode . ': ' . $responseBody);
            $invitation->setTrustpilotResponse($responseBody);
            $this->entityManager->flush();

            $this->logger->error('Trustpilot: Invitation API returned error', [
                'status_code' => $statusCode,
                'response' => $responseBody,
            ]);

            return false;
        } catch (\Exception $e) {
            $invitation->setStatus(InvitationLog::STATUS_FAILED);
            $invitation->setErrorMessage($e->getMessage());
            $this->entityManager->flush();

            $this->logger->error('Trustpilot: Failed to send invitation', [
                'error' => $e->getMessage(),
                'user_email' => $invitation->getUserEmail(),
            ]);

            return false;
        }
    }

    public function processPendingInvitations(): int
    {
        $pending = $this->invitationLogRepository->findPendingInvitations();
        $sentCount = 0;

        foreach ($pending as $invitation) {
            if ($this->sendInvitation($invitation)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    // ──────────────────────────────────────────────
    // Configuration Helpers
    // ──────────────────────────────────────────────

    public function getSettings(): array
    {
        return [
            'enabled' => (bool) $this->getSetting('enabled', true),
            'review_url' => $this->getSetting('review_url', ''),
            'test_mode' => (bool) $this->getSetting('test_mode', false),
            'api_key' => $this->getSetting('api_key', ''),
            'business_unit_id' => $this->getSetting('business_unit_id', ''),
            'business_domain' => $this->getSetting('business_domain', ''),
            'afs_enabled' => (bool) $this->getSetting('afs_enabled', false),
            'afs_send_mode' => $this->getSetting('afs_send_mode', 'immediate'),
            'enable_widget' => (bool) $this->getSetting('enable_widget', true),
            'display_mode' => $this->getSetting('display_mode', 'custom'),
            'popup_title' => $this->getSetting('popup_title', 'Enjoying our service?'),
            'popup_message' => $this->getSetting('popup_message', ''),
            'show_leave_review_button' => (bool) $this->getSetting('show_leave_review_button', true),
            'trustbox_template_id' => $this->getSetting('trustbox_template_id', '53aa8912dec7e10d38f59f36'),
            'trustbox_theme' => $this->getSetting('trustbox_theme', 'light'),
            'trustbox_height' => $this->getSetting('trustbox_height', '140px'),
            'trustbox_stars' => $this->getSetting('trustbox_stars', '4,5'),
            'carousel_review_count' => (int) $this->getSetting('carousel_review_count', 5),
            'carousel_min_stars' => (int) $this->getSetting('carousel_min_stars', 4),
        ];
    }

    public function isAfsConfigured(): bool
    {
        return !empty($this->getSetting('api_key'))
            && !empty($this->getSetting('api_secret'))
            && ($this->resolveBusinessUnitId() !== null);
    }

    public function validateApiConfiguration(): array
    {
        $errors = [];

        if (empty($this->getSetting('api_key'))) {
            $errors[] = 'Trustpilot API key is not configured';
        }

        if (empty($this->getSetting('api_secret'))) {
            $errors[] = 'Trustpilot API secret is not configured';
        }

        if (empty($this->getSetting('business_unit_id')) && empty($this->getSetting('business_domain'))) {
            $errors[] = 'Neither Business Unit ID nor Business Domain is configured';
        }

        return $errors;
    }

    public function clearCache(): bool
    {
        try {
            $buId = $this->resolveBusinessUnitId();
            if ($buId) {
                $this->cache->delete('trustpilot_data_' . md5($buId));
                $this->cache->delete('trustpilot_reviews_' . md5($buId . '_' . $this->getSetting('carousel_review_count', 5) . '_' . implode(',', range((int) $this->getSetting('carousel_min_stars', 4), 5))));
            }
            $this->cache->delete(self::TOKEN_CACHE_KEY);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot: Failed to clear cache', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->pluginSettingService->get(self::PLUGIN_ID, $key, $default);
    }

    private function getDefaultData(): array
    {
        return [
            'score' => 0,
            'stars' => 0,
            'reviews_count' => 0,
        ];
    }
}
