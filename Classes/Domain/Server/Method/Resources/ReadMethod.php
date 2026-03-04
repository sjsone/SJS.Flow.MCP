<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\Resources;
use SJS\Flow\MCP\Domain\Server\Method\Resources\ReadMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class ReadMethod
{
    /**
     * @param array<mixed> $resources
     */
    public static function handle(Resources\ReadRequest $resourcesListRequest, array $resources): string
    {
        $response = new Response($resourcesListRequest->id);
        return $response->result(new Result($resources));
    }
}
