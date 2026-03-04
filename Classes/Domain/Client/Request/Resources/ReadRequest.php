<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Resources;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

#[Flow\Proxy(false)]
class ReadRequest
{
    public const Method = "resources/read";

    public function __construct(
        public readonly int $id,
        public readonly string $uri
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

        $uri = $params['uri'] ?? null;
        if (!\is_string($uri)) {
            throw new \InvalidArgumentException("request param 'uri' must be a string");
        }

        return new self(
            $id,
            $uri
        );
    }
}
