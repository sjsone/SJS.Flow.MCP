<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Transport\JsonRPC\Request;

use Neos\Flow\Annotations as Flow;

/**
 * MCP protocol metadata carried in each request via the _meta field.
 *
 * Introduced in MCP spec 2026-07-28 as part of the shift to a stateless
 * protocol. Replaces the old initialize/initialized handshake by carrying
 * protocol version, client identity, and client capabilities on every request.
 */
#[Flow\Proxy(false)]
class Meta
{
    /**
     * @param string $protocolVersion The MCP protocol version the client is using (e.g. "2026-07-28")
     * @param array<string,mixed>|null $clientInfo Optional client identity (name, version)
     * @param array<string,mixed>|null $capabilities Optional client capabilities
     */
    public function __construct(
        public readonly string $protocolVersion,
        public readonly array|null $clientInfo = null,
        public readonly array|null $capabilities = null,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $protocolVersion = $data['protocolVersion'] ?? null;
        if (!\is_string($protocolVersion) || $protocolVersion === '') {
            throw new \InvalidArgumentException('_meta.protocolVersion must be a non-empty string');
        }

        $clientInfo = null;
        if (isset($data['clientInfo']) && \is_array($data['clientInfo'])) {
            $clientInfo = $data['clientInfo'];
        }

        $capabilities = null;
        if (isset($data['capabilities']) && \is_array($data['capabilities'])) {
            $capabilities = $data['capabilities'];
        }

        return new self(
            $protocolVersion,
            $clientInfo,
            $capabilities,
        );
    }
}
