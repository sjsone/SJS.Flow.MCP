<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Resources;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

#[Flow\Proxy(false)]
class ListRequest
{
    public const Method = "resources/list";

    public function __construct(
        public readonly int $id,
        public readonly ?string $cursor = null,
    ) {
    }

    public static function fromJsonRPCRequest(Request $request): self
    {
        return new self(
            $request->id,
            $request->params['cursor'] ?? null
        );
    }
}
