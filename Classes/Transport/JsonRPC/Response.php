<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Transport\JsonRPC;

use Neos\Flow\Annotations as Flow;

enum ErrorCode: int
{
    // Invalid JSON was received by the server.
    case PARSE_ERROR = -32700;

    // The JSON sent is not a valid Request object.
    case INVALID_REQUEST = -32600;

    // The method does not exist / is not available.
    case METHOD_NOT_FOUND = -32601;

    // Invalid method parameter(s).
    case INVALID_PARAMS = -32602;

    // Internal JSON-RPC error.
    case INTERNAL_ERROR = -32603;

    // // -32000 to -32099	Server error	Reserved for implementation-defined server-errors.
    // case SERVER_ERROR_MIN = -32000;
    // case SERVER_ERROR_MAX = -32099;
}


#[Flow\Proxy(false)]
class Response
{
    public function __construct(
        public readonly int $id
    ) {
    }

    public function result(\JsonSerializable $data): string
    {
        $result = json_encode([
            "jsonrpc" => "2.0",
            "id" => $this->id,
            "result" => $data
        ]);

        if ($result === false) {
            throw new \InvalidArgumentException("Result data is not json encodable");
        }

        return $result;
    }

    public function error(string $message, ErrorCode $code): string
    {
        $error = json_encode([
            "jsonrpc" => "2.0",
            "id" => $this->id,
            "error" => [
                "code" => $code->value,
                "message" => $message
            ]
        ]);

        if ($error === false) {
            throw new \InvalidArgumentException("Result data is not json encodable");
        }

        return $error;
    }
}
