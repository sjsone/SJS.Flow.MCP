<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Connection;

interface ConnectionProviderInterface
{
    public function getConnectionByTokenAndServerName(string $token, string $serverName): ?Connection;
}
