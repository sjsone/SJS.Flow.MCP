<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Controller;

use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\NoSuchArgumentException;
use Neos\Flow\Security\Context;
use Psr\Log\LoggerInterface;
use SJS\Flow\MCP\Domain\Server\Server;
use SJS\Flow\MCP\Domain\Server\ServerFactory;
use Neos\Flow\Annotations as Flow;

class MCPController extends ActionController
{
    #[Flow\Inject()]
    protected ServerFactory $serverFactory;

    #[Flow\Inject(name: "SJS.Flow.MCP:MCPLogger", lazy: false)]
    protected LoggerInterface $mcpLogger;

    #[Flow\Inject()]
    protected Context $securityContext;

    /**
     * @var array<string>
     */
    protected $supportedMediaTypes = [
        'application/json',
        // 'text/event-stream',
    ];

    /**
     * @Flow\SkipCsrfProtection
     */
    public function mcpAction(): string
    {
        $this->response->setHttpHeader("Content-Type", "application/json");

        $this->mcpLogger->info(\sprintf("account: %s\n", $this->securityContext->getAccount()?->getAccountIdentifier() ?? "none!"));

        $server = $this->buildServerFromRequest();
        if ($server === null) {
            throw new \Exception("Could not build server from request");
        }

        $this->mcpLogger->info(\sprintf("Built server: %s\n", $server->name));

        $response = $server->handleRequest();

        return $response;
    }

    protected function buildServerFromRequest(): ?Server
    {
        if ($this->securityContext->getAccount() === null) {
            $this->mcpLogger->warning("Creating empty server due to missing account");
            return $this->serverFactory->buildEmpty($this->request);
        }

        return $this->serverFactory->buildFromActionRequest(
            $this->request
        );
    }
}
