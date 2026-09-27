<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\InitializeMethod;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Protocol;

/**
 * @deprecated since MCP 2026-07-28: the initialize result is replaced by
 *             DiscoverMethod\Result returned from the server/discover RPC.
 * @see \SJS\Flow\MCP\Domain\Server\Method\DiscoverMethod\Result
 */
#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {

        return [
            "protocolVersion" => Protocol\Version::MCP_2026_07_28->value,
            "capabilities" => [
                "resources" => [
                    "listChanged" => false,
                    "subscribe" => false,
                ],
                "completions" => (object) [],
                "tools" => (object) [],
            ],
            "instructions" => "do stuff",
            "serverInfo" => [
                "name" => "Neos MCP",
                "version" => "0.0.1",
            ]
        ];
    }
}
