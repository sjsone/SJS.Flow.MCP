<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Security\Account;
use Psr\Log\LoggerInterface;
use SJS\Flow\MCP\Domain\Connection\Connection;
use SJS\Flow\MCP\Domain\Connection\ConnectionProviderInterface;
use SJS\Flow\MCP\Domain\Connection\ServerContext;
use SJS\Flow\MCP\Domain\Server\Server;

#[Flow\Scope('singleton')]
class ServerFactory
{
    /**
     * @var array<string,array<string,mixed>>
     */
    #[Flow\InjectConfiguration(path: 'server')]
    protected array $configuration;

    #[Flow\Inject]
    protected ObjectManager $objectManager;

    #[Flow\Inject]
    protected ConnectionProviderInterface $connectionProvider;

    #[Flow\Inject(name: "SJS.Flow.MCP:MCPLogger", lazy: false)]
    protected LoggerInterface $mcpLogger;

    public function buildEmpty(ActionRequest $actionRequest): Server
    {
        $connection = new Connection("", new Account(), "");
        $serverContext = new ServerContext(connection: $connection, request: $actionRequest);
        $configuration = new Server\Configuration([], []);

        return new Server(
            "empty",
            $configuration,
            $serverContext,
            $this->objectManager,
            $this->mcpLogger
        );
    }

    public function buildFromActionRequest(ActionRequest $actionRequest): ?Server
    {
        $name = $this->extractServerNameFromActionRequest($actionRequest);

        if (!\array_key_exists($name, $this->configuration)) {
            throw new \InvalidArgumentException("provided server does not exist");
        }

        $connection = $this->connectionProvider->getConnectionByTokenAndServerName(
            $this->extractTokenFromActionRequest($actionRequest),
            $name
        );

        if ($connection === null) {
            return null;
        }

        $serverContext = new ServerContext(connection: $connection, request: $actionRequest);

        return new Server(
            $name,
            Server\Configuration::fromArray($this->configuration[$name]),
            $serverContext,
            $this->objectManager,
            $this->mcpLogger
        );
    }

    public static function extractServerNameFromActionRequest(ActionRequest $actionRequest): string
    {
        try {
            $serverName = $actionRequest->getArgument("serverName");
        } catch (NoSuchArgumentException) {
            throw new \Exception("missing server name");
        }

        if (\is_array($serverName)) {
            throw new \Exception("server name must not be an array");
        }

        $serverName = \trim($serverName);
        if ($serverName === "") {
            throw new \Exception("server name must not be empty");
        }

        return $serverName;
    }

    protected function extractTokenFromActionRequest(ActionRequest $actionRequest): string
    {
        $httpRequest = $actionRequest->getHttpRequest();
        $authHeader = $httpRequest->getHeader('Authorization');
        if (empty($authHeader)) {
            return '';
        }
        $authHeader = $authHeader[0];
        if (\str_starts_with($authHeader, 'Bearer ')) {
            return \substr($authHeader, 7);
        }
        return '';
    }
}
