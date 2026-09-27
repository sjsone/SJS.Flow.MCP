<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\InitializeRequest;
use SJS\Flow\MCP\Domain\Server\Method\InitializeMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

/**
 * @deprecated since MCP 2026-07-28: the initialize/initialized handshake is retired.
 *             Use DiscoverMethod for the new server/discover capability discovery RPC.
 * @see DiscoverMethod
 */
#[Flow\Proxy(false)]
class InitializeMethod
{
    public static function handle(InitializeRequest $initializeRequest): string
    {
        $response = new Response($initializeRequest->id);
        return $response->result(new Result());
    }
}
