<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

/**
 * Request for the server/discover RPC (MCP 2026-07-28).
 *
 * Stateless capability discovery. Clients call this to learn about the server's
 * capabilities, protocol version, and configuration without a prior handshake.
 *
 * This is the replacement for the deprecated initialize handshake.
 */
#[Flow\Proxy(false)]
class DiscoverRequest
{
    public const Method = "server/discover";

    public function __construct(
        public readonly ?int $id,
    ) {
    }

    public static function fromJsonRPCRequest(Request $request): self
    {
        $id = $request->id;
        if ($id === null) {
            throw new \InvalidArgumentException("id in request is null");
        }

        return new self($id);
    }
}
