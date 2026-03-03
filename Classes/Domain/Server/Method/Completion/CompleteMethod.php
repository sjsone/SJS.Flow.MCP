<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Completion;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request;
use SJS\Flow\MCP\Domain\MCP\Completion;
use SJS\Flow\MCP\Domain\Server\Method\Completion\CompleteMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class CompleteMethod
{
    public static function handle(Request\Completion\CompleteRequest $completionCompleteRequest, Completion $completion): string
    {
        $response = new Response($completionCompleteRequest->id);
        return $response->result(new Result($completion));
    }
}
