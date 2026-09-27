<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Client\Request\Notifications;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Transport\JsonRPC\Request;

/**
 * @deprecated since MCP 2026-07-28: the initialize/initialized handshake is retired.
 *             The protocol is now stateless — no post-initialize notification is needed.
 */
#[Flow\Proxy(false)]
class Initialized
{
    public const Method = "notifications/initialized";

    public function __construct()
    {
    }

    public static function fromJsonRPCRequest(Request $request): self
    {
        return new self();
    }
}
