<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Argument
{
    public function __construct(
        public readonly string $name,
        public readonly string $value,
    ) {
    }

    /**
     * @param array<mixed,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $name = $data['name'] ?? null;
        if (!\is_string($name)) {
            throw new \InvalidArgumentException("'name' must be set and a string");
        }

        $value = $data['value'] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException("'value' must be set and a string");
        }

        return new self(
            $name,
            $value,
        );
    }
}
