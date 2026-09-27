<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\DiscoverRequest;
use SJS\Flow\MCP\Domain\Server\Method\DiscoverMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;
use SJS\Flow\MCP\Domain\Protocol;

/**
 * Handler for the server/discover RPC (MCP 2026-07-28).
 *
 * Stateless capability discovery. Returns server capabilities, protocol
 * version, and server info without requiring a prior initialize handshake.
 *
 * @see \SJS\Flow\MCP\Domain\Server\Method\InitializeMethod The deprecated initialize (still works for old clients)
 */
#[Flow\Proxy(false)]
class DiscoverMethod
{
    public static function handle(DiscoverRequest $discoverRequest): string
    {
        $response = new Response($discoverRequest->id);

        return $response->result(new Result(
            capabilities: [
                "resources" => [
                    "listChanged" => false,
                    "subscribe" => false,
                ],
                "completions" => (object) [],
                "tools" => (object) [],
            ],
            protocolVersion: Protocol\Version::MCP_2026_07_28->value,
            serverInfo: [
                "name" => "Neos MCP",
                "version" => "0.0.1",
            ],
            instructions: "Use tools/list, resources/list, and resources/read to interact with the server.",
        ));
    }
}
