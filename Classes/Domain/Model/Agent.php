<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;

class Agent
{
    protected function __construct(
        public readonly string $name,
        public readonly Account $account,
        public readonly string $token,
    ) {

    }

    public static function create(
        string $name,
        Account $account,
        string $token
    ): self {
        return new Agent(
            $name,
            $account,
            $token
        );
    }
}
