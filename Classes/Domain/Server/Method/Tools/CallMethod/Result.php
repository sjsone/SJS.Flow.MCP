<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Tools\CallMethod;

use Neos\Flow\Annotations as Flow;

// TODO: check if needed
#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    public function __construct(
        public readonly mixed $content,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'content' => $this->content
        ];
    }
}
