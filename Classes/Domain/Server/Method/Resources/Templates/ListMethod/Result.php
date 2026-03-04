<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources\Templates\ListMethod;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    /**
     * @param array<mixed> $resourceTemplates
     */
    public function __construct(
        public readonly array $resourceTemplates,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            "resourceTemplates" => $this->resourceTemplates,
        ];
    }
}
