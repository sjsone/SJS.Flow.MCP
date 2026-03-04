<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Resources\Templates;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\Client\Request\Resources;
use SJS\Flow\MCP\Domain\Server\Method\Resources\Templates\ListMethod\Result;
use SJS\Flow\MCP\Transport\JsonRPC\Response;

#[Flow\Proxy(false)]
class ListMethod
{
    /**
     * @param array<mixed> $templates
     */
    public static function handle(Resources\Templates\ListRequest $resourcesTemplatesListRequest, array $templates): string
    {
        $response = new Response($resourcesTemplatesListRequest->id);
        return $response->result(new Result($templates));
    }
}
