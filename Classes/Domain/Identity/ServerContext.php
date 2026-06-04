<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Identity;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;

#[Flow\Proxy(false)]
class ServerContext
{
    public function __construct(
        public readonly ?Identity $identity,
        public readonly ActionRequest $request,
    ) {
    }
}
