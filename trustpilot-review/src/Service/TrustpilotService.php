<?php

namespace Plugins\TrustpilotReview\Service;

use App\Core\Service\Plugin\PluginSettingService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service for Trustpilot Review plugin business logic.
 *
 * Handles fetching Trustpilot ratings and reviews count.
 */
class TrustpilotService
{
    private const CACHE_TTL = 3600; // Cache for 1 hour

    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {}

    /**
     * Get Trustpilot data (score and reviews count) from the review URL.
     *
     * @param string $reviewUrl The Trustpilot review URL
     * @return array{score: float, reviews_count: int}
     */
    public function getTrustpilotData(string $reviewUrl): array
    {
        $businessName = $this->extractBusinessName($reviewUrl);
        
        if (empty($businessName)) {
            $this->logger->warning('Trustpilot Review: Could not extract business name from URL', [
                'url' => $reviewUrl,
            ]);
            return $this->getDefaultData();
        }

        $cacheKey = 'trustpilot_data_' . md5($businessName);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($businessName) {
                $item->expiresAfter(self::CACHE_TTL);
                return $this->fetchTrustpilotData($businessName);
            });
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot Review: Failed to fetch data', [
                'business' => $businessName,
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultData();
        }
    }

    /**
     * Extract business name from Trustpilot URL.
     *
     * @param string $url The Trustpilot URL
     * @return string|null The business name or null if not found
     */
    private function extractBusinessName(string $url): ?string
    {
        // Handle various Trustpilot URL formats:
        // https://www.trustpilot.com/review/example.com
        // https://www.trustpilot.com/evaluate/example.com
        // https://fr.trustpilot.com/review/example.com
        
        $patterns = [
            '#trustpilot\.com/review/([^/?]+)#i',
            '#trustpilot\.com/evaluate/([^/?]+)#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Fetch Trustpilot data from their public page.
     *
     * @param string $businessName The business name/domain
     * @return array{score: float, reviews_count: int}
     */
    private function fetchTrustpilotData(string $businessName): array
    {
        try {
            $url = 'https://www.trustpilot.com/review/' . urlencode($businessName);
            
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; TrustpilotWidget/1.0)',
                    'Accept' => 'text/html',
                ],
                'timeout' => 10,
            ]);

            $html = $response->getContent();

            // Extract score from JSON-LD structured data
            $score = $this->extractScoreFromHtml($html);
            $reviewsCount = $this->extractReviewsCountFromHtml($html);

            $this->logger->info('Trustpilot Review: Successfully fetched data', [
                'business' => $businessName,
                'score' => $score,
                'reviews_count' => $reviewsCount,
            ]);

            return [
                'score' => $score,
                'reviews_count' => $reviewsCount,
            ];
        } catch (\Exception $e) {
            $this->logger->warning('Trustpilot Review: HTTP request failed', [
                'business' => $businessName,
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultData();
        }
    }

    /**
     * Extract score from Trustpilot HTML page.
     *
     * @param string $html The HTML content
     * @return float The extracted score or default value
     */
    private function extractScoreFromHtml(string $html): float
    {
        // Try to extract from JSON-LD structured data
        if (preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $matches)) {
            $jsonContent = $matches[1];
            $data = json_decode($jsonContent, true);
            
            if (isset($data['aggregateRating']['ratingValue'])) {
                return (float) $data['aggregateRating']['ratingValue'];
            }
        }

        // Fallback: try to extract from meta tags or data attributes
        if (preg_match('/data-rating="([0-9.]+)"/i', $html, $matches)) {
            return (float) $matches[1];
        }

        // Another fallback: look for rating in common patterns
        if (preg_match('/"ratingValue"\s*:\s*"?([0-9.]+)"?/i', $html, $matches)) {
            return (float) $matches[1];
        }

        return 0; // Default fallback - no score available
    }

    /**
     * Extract reviews count from Trustpilot HTML page.
     *
     * @param string $html The HTML content
     * @return int The extracted reviews count or default value
     */
    private function extractReviewsCountFromHtml(string $html): int
    {
        // Try to extract from JSON-LD structured data
        if (preg_match('/<script[^>]*type="application\/ld\+json"[^>]*>(.*?)<\/script>/is', $html, $matches)) {
            $jsonContent = $matches[1];
            $data = json_decode($jsonContent, true);
            
            if (isset($data['aggregateRating']['reviewCount'])) {
                return (int) $data['aggregateRating']['reviewCount'];
            }
        }

        // Fallback: try common patterns
        if (preg_match('/"reviewCount"\s*:\s*"?([0-9]+)"?/i', $html, $matches)) {
            return (int) $matches[1];
        }

        // Look for total reviews text patterns
        if (preg_match('/([0-9,]+)\s*(?:avis|reviews?|total)/i', $html, $matches)) {
            return (int) str_replace(',', '', $matches[1]);
        }

        return 0; // Default fallback
    }

    /**
     * Get default data when fetching fails or no reviews exist.
     *
     * @return array{score: float, reviews_count: int}
     */
    private function getDefaultData(): array
    {
        return [
            'score' => 0,
            'reviews_count' => 0,
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
            'review_url' => $this->pluginSettingService->get('trustpilot-review', 'review_url', ''),
            'enable_widget' => (bool) $this->pluginSettingService->get('trustpilot-review', 'enable_widget', true),
            'popup_title' => $this->pluginSettingService->get('trustpilot-review', 'popup_title', 'Vous appréciez notre service ?'),
            'popup_message' => $this->pluginSettingService->get('trustpilot-review', 'popup_message', ''),
        ];
    }

    /**
     * Clear the cached Trustpilot data.
     *
     * @param string $reviewUrl The review URL to clear cache for
     * @return bool True if cache was cleared
     */
    public function clearCache(string $reviewUrl): bool
    {
        $businessName = $this->extractBusinessName($reviewUrl);
        if (empty($businessName)) {
            return false;
        }

        try {
            $cacheKey = 'trustpilot_data_' . md5($businessName);
            $this->cache->delete($cacheKey);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Trustpilot Review: Failed to clear cache', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
