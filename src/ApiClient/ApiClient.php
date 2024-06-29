<?php declare(strict_types=1);

namespace App\ApiClient;

use App\Exception\ApiClientException;
use GuzzleHttp\Client;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class ApiClient implements ApiClientInterface
{
    private const int HTTP_OK = 200;

    public function __construct(
        private readonly Client $client,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws ApiClientException
     */
    public function get(
        string $url,
        int $cacheTTL,
        array $options = [],
        string $bin = ''
    ): string {
        $options['http_errors'] = false;

        $cacheKey = $this->generateCacheKey($url, $bin);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($url, $cacheTTL, $options, $bin) {
            $item->expiresAfter($cacheTTL);

            $response = $this->client->get($url.$bin, $options);

            if ($response->getStatusCode() !== self::HTTP_OK) {
                throw new ApiClientException(
                    errorMessage: sprintf('Unexpected status code from API %s', $url),
                    statusCode: $response->getStatusCode()
                );
            }

            return $response->getBody()->getContents();
        });
    }

    private function generateCacheKey(string $url, string $bin): string
    {
        return 'rates_'.md5($url.$bin);
    }
}
