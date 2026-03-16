<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Completion;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest\Argument;
use SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest\Ref;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

#[Flow\Proxy(false)]
class CompleteRequest
{
    public const Method = "completion/complete";

    public function __construct(
        public readonly int $id,
        public readonly Argument $argument,
        public readonly Ref $ref
    ) {
    }

    public static function fromJsonRPCRequest(Request $request): self
    {
        $id = $request->id;
        if ($id === null) {
            throw new \InvalidArgumentException("id in request is null");
        }

        $params = $request->params ?? [];
        if (!\is_array($params)) {
            throw new \InvalidArgumentException("request params must an array");
        }

        $argument = $params['argument'] ?? null;
        if (!\is_array($argument)) {
            throw new \InvalidArgumentException("request param 'argument' must be an array");
        }

        $ref = $params['ref'] ?? null;
        if (!\is_array($ref)) {
            throw new \InvalidArgumentException("request param 'ref' must be an array");
        }

        return new self(
            $id,
            Argument::fromArray($argument),
            Ref::fromArray($ref),
        );
    }
}
