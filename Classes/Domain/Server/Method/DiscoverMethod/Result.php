<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\DiscoverMethod;



use Neos\Flow\Annotations as Flow;

/**
 * Result for the server/discover RPC (MCP 2026-07-28).
 *
 * Replaces the old initialize response. Returns server capabilities,
 * protocol version, and instructions as a stateless query.
 */
#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $capabilities Server capabilities
     * @param string $protocolVersion The supported protocol version
     * @param array<string,mixed>|null $serverInfo Optional server identity
     * @param string|null $instructions Optional human-readable instructions
     */
    public function __construct(
        public readonly array $capabilities = [],
        // TODO: instead of using a magic number for protocol version, put it in an ENUM 
        public readonly string $protocolVersion = "",
        public readonly ?array $serverInfo = null,
        public readonly ?string $instructions = null,
    ) {

    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            "protocolVersion" => $this->protocolVersion,
            "capabilities" => $this->capabilities,
        ];

        if ($this->serverInfo !== null) {
            $data["serverInfo"] = $this->serverInfo;
        }

        if ($this->instructions !== null) {
            $data["instructions"] = $this->instructions;
        }

        return $data;
    }
}
