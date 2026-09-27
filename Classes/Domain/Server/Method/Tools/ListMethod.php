<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Tools;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\Tools;
use SJS\Flow\MCP\Domain\Server\Method\Tools\ListMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class ListMethod
{
    /**
     * @param array<\SJS\Flow\MCP\Domain\MCP\Tool> $tools
     * @param null|int $ttlMs Time-to-live in milliseconds for cache control (MCP 2026-07-28)
     * @param null|string $cacheScope Cache scope: "server" or "connection" (MCP 2026-07-28)
     */
    public static function handle(
        Tools\ListRequest $toolsListRequest,
        array $tools,
        ?string $nextCursor,
        ?int $ttlMs = null,
        ?string $cacheScope = null,
    ): string {
        $response = new Response($toolsListRequest->id);
        return $response->result(new Result($tools, $nextCursor, $ttlMs, $cacheScope));
    }
}
