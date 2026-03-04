<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Tools;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

#[Flow\Proxy(false)]
class ListRequest
{
    public const Method = "tools/list";

    public function __construct(
        public readonly int $id,
        public readonly ?string $cursor = null,
    ) {
    }

    public static function fromJsonRPCRequest(Request $request): self
    {
        $id = $request->id;
        if ($id === null) {
            throw new \InvalidArgumentException("id in request is null");
        }

        $params = $request->params;
        if (!\is_array($params)) {
            throw new \InvalidArgumentException("request params must an array");
        }

        $cursor = $params['cursor'] ?? null;
        if (!\is_string($cursor)) {
            throw new \InvalidArgumentException("request param 'cursor' must be string");
        }

        return new self(
            $id,
            $cursor
        );
    }
}
