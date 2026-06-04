<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Identity;

interface IdentityProviderInterface
{
    public function getIdentityByTokenAndServerName(string $token, string $serverName): ?Identity;
}
