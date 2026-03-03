<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\InitializeRequest;
use SJS\Flow\MCP\Domain\Server\Method\InitializeMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class InitializeMethod
{
    public static function handle(InitializeRequest $initializeRequest): string
    {

        $response = new Response($initializeRequest->id);



        return $response->result(new Result());
    }
}
