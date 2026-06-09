<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Controller;

use Neos\Flow\Mvc\Controller\ActionController;
use GuzzleHttp\Psr7\Response;
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
    public function mcpAction(): Response
    {
        $this->mcpLogger->info(\sprintf("account: %s\n", $this->securityContext->getAccount()?->getAccountIdentifier() ?? "none!"));

        $server = $this->buildServerFromRequest();
        if ($server === null) {
            return (new Response(status: 401, body: "Authorization missing"))
                ->withAddedHeader("Content-Type", "text/html");
        }

        $this->mcpLogger->info(\sprintf("Built server: %s\n", $server->name));

        $responseBody = $server->handleRequest();
        return (new Response(status: 200, body: $responseBody))
            ->withAddedHeader("Content-Type", "application/json");
    }

    protected function buildServerFromRequest(): ?Server
    {
        return $this->serverFactory->buildFromActionRequest(
            $this->request
        );
    }
}
