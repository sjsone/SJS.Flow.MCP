<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\Resources;
use SJS\Flow\MCP\Domain\Server\Method\Resources\ListMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class ListMethod
{
    /**
     * @param array<mixed> $resources
     */
    public static function handle(Resources\ListRequest $resourcesListRequest, array $resources, ?string $nextCursor): string
    {
        $response = new Response($resourcesListRequest->id);
        return $response->result(new Result($resources, $nextCursor));
    }
}
