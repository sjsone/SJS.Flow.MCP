<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Connection;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;

#[Flow\Proxy(false)]
class ServerContext
{
    public function __construct(
        public readonly ?Connection $connection,
        public readonly ActionRequest $request,
    ) {
    }
}
