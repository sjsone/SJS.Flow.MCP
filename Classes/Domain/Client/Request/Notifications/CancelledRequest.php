<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Notifications;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

#[Flow\Proxy(false)]
class CancelledRequest
{
    public const Method = "notifications/cancelled";

    public function __construct()
    {
    }

    public static function fromJsonRPCRequest(Request $request): self
    {
        return new self();
    }
}
