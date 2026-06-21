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
    #[Flow\Inject]
    protected ServerFactory $serverFactory;

    #[Flow\Inject(name: "SJS.Flow.MCP:MCPLogger", lazy: false)]
    protected LoggerInterface $mcpLogger;

    #[Flow\Inject]
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
    public function mcpAction(): Response|string
    {
        $this->mcpLogger->info(\sprintf("account: %s\n", $this->securityContext->getAccount()?->getAccountIdentifier() ?? "none!"));

        $server = $this->buildServerFromRequest();
        if ($server === null) {
            $responseBody = "Authorization missing";
            $status = 401;
            $contentType = "text/html";

            if ($this->isLegacy()) {
                $this->response->setStatusCode($status);
                $this->response->setContentType($contentType);
                return $responseBody;
            }

            return (new Response(status: $status, body: $body))
                ->withAddedHeader("Content-Type", $contentType);
        }

        $this->mcpLogger->info(\sprintf("Built server: %s\n", $server->name));

        $responseBody = $server->handleRequest();
        $status = 200;
        $contentType = "application/json";

        if ($this->isLegacy()) {
            $this->response->setStatusCode($status);
            $this->response->setContentType($contentType);
            return $responseBody;
        }

        return (new Response(status: $status, body: $responseBody))
            ->withAddedHeader("Content-Type", $contentType);
    }

    protected function isLegacy(): bool
    {
        return str_starts_with(FLOW_VERSION_BRANCH, '8.');
    }

    protected function buildServerFromRequest(): ?Server
    {
        return $this->serverFactory->buildFromActionRequest(
            $this->request
        );
    }
}
