<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\MCP;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Completion implements \JsonSerializable
{
    /**
     * @param array<mixed> $values
     */
    public function __construct(
        public readonly array $values,
        public readonly int $total,
        public readonly bool $hasMore,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'values' => $this->values,
            'total' => $this->total,
            'hasMore' => $this->hasMore,
        ];
    }
}
