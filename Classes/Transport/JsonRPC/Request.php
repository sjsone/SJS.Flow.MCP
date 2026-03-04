<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Transport\JsonRPC;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class Request
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $method,
        public readonly mixed $params = null
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertRequestData($data);

        $id = $data['id'] ?? null;
        if (!\is_int($id)) {
            throw new \InvalidArgumentException("id must be int");
        }

        $method = $data['method'];
        if (!\is_string($method)) {
            throw new \InvalidArgumentException("method must be a string");
        }

        $params = $data['params'] ?? null;

        return new self(
            $id,
            $method,
            $params,
        );
    }

    /**
     * @param array<string,mixed> $data
     */
    protected static function assertRequestData(array $data, bool $withId = false): void
    {
        $jsonRpc = $data['jsonrpc'] ?? null;
        if ($jsonRpc === null) {
            throw new \Exception("jsonrpc is missing");
        }
        if ($jsonRpc !== "2.0") {
            throw new \Exception("jsonrpc is not 2.0");
        }

        $method = $data['method'] ?? null;
        if ($method === null) {
            throw new \Exception("method required");
        }

        if ($withId) {
            $id = $data['id'] ?? null;
            if ($id === null) {
                throw new \Exception("id required");
            }
        }
    }

}
