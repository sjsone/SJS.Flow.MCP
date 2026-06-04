<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Connection;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;

#[Flow\Proxy(false)]
class Connection
{
    public function __construct(
        public readonly string $connectionName,
        public readonly Account $account,
        public readonly string $token,
    ) {
    }

    public static function create(
        string $connectionName,
        Account $account,
        string $token
    ): self {
        return new self($connectionName, $account, $token);
    }
}
