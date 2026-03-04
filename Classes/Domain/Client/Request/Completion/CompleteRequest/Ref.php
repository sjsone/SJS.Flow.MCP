<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Ref
{
    public function __construct(
        // TODO: create enum for completion/complete request ref.type
        public readonly string $type,
        public readonly string $uri,
    ) {
    }

    /**
     * @param array<mixed,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? null;
        if (!\is_string($type)) {
            throw new \InvalidArgumentException("'type' must be set and a string");
        }

        $uri = $data['uri'] ?? null;
        if (!\is_string($uri)) {
            throw new \InvalidArgumentException("'uri' must be set and a string");
        }

        return new self(
            $type,
            $uri,
        );
    }
}
