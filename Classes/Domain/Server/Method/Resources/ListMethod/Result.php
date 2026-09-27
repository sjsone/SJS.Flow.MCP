<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources\ListMethod;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    /**
     * @param array<mixed> $resources
     * @param null|int $ttlMs Time-to-live in milliseconds for cache control (MCP 2026-07-28)
     * @param null|string $cacheScope Cache scope: "server" or "connection" (MCP 2026-07-28)
     */
    public function __construct(
        public readonly array $resources,
        public readonly ?string $nextCursor,
        public readonly ?int $ttlMs = null,
        public readonly ?string $cacheScope = null,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            "resources" => $this->resources,
        ];

        if ($this->nextCursor) {
            $data['nextCursor'] = $this->nextCursor;
        }

        if ($this->ttlMs !== null) {
            $data['ttlMs'] = $this->ttlMs;
        }

        if ($this->cacheScope !== null) {
            $data['cacheScope'] = $this->cacheScope;
        }

        return $data;
    }
}
