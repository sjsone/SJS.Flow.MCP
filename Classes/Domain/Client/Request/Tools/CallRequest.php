<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Tools;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

#[Flow\Proxy(false)]
class CallRequest
{
    public const Method = "tools/call";

    /**
     * @param array<mixed,mixed> $arguments
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly array $arguments,
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
            throw new \InvalidArgumentException("request params must be an array");
        }

        $name = $params['name'] ?? null;
        if (!\is_string($name)) {
            throw new \InvalidArgumentException("request param 'arguments' must be a string");
        }

        $paramArguments = $params['arguments'] ?? null;
        if (!\is_array($paramArguments)) {
            throw new \InvalidArgumentException("request param 'arguments' must be an array");
        }

        return new self(
            $id,
            $name,
            $paramArguments,
        );
    }
}
