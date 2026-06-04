<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Provider;

use SJS\Flow\MCP\Domain\Model\Connection;

class ConfigurationConnectionProvider implements ConnectionProviderInterface
{
    public function initialize(): void
    {
    }

    public function getConnectionByTokenAndServerName(string $token, string $serverName): ?Connection
    {
        return null;
    }
}
