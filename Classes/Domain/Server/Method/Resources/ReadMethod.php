<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\Resources;
use SJS\Flow\MCP\Domain\Server\Method\Resources\ReadMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class ReadMethod
{
    /**
     * @param array<\SJS\Flow\MCP\Domain\MCP\Resource> $resources
     * @param null|int $ttlMs Time-to-live in milliseconds for cache control (MCP 2026-07-28)
     * @param null|string $cacheScope Cache scope: "server" or "connection" (MCP 2026-07-28)
     */
    public static function handle(
        Resources\ReadRequest $resourcesListRequest,
        array $resources,
        ?int $ttlMs = null,
        ?string $cacheScope = null,
    ): string {
        $response = new Response($resourcesListRequest->id);
        return $response->result(new Result($resources, $ttlMs, $cacheScope));
    }
}
