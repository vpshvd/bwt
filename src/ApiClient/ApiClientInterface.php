<?php declare (strict_types=1);

namespace App\ApiClient;

interface ApiClientInterface
{
    public function get(string $url, int $cacheTTL, array $options = [], string $bin = ''): string;
}
