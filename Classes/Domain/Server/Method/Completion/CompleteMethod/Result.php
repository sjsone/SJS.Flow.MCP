<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Completion\CompleteMethod;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\MCP\Completion;

#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    public function __construct(
        public readonly Completion $completion,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            "completion" => $this->completion
        ];
    }
}
