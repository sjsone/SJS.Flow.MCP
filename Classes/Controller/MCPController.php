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

        // Extract header-based routing headers (MCP 2026-07-28, SEP-2243)
        $mcpMethod = $this->extractHttpHeader('Mcp-Method');
        $mcpName = $this->extractHttpHeader('Mcp-Name');
        if ($mcpMethod !== null) {
            $this->mcpLogger->info(\sprintf("Mcp-Method: %s, Mcp-Name: %s", $mcpMethod, $mcpName ?? 'none'));
        }

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

            return (new Response(status: $status, body: $responseBody))
                ->withAddedHeader("Content-Type", $contentType);
        }

        $this->mcpLogger->info(\sprintf("Built server: %s\n", $server->name));

        $responseBody = $server->handleRequest();
        $status = 200;
        $contentType = "application/json";

        if ($this->isLegacy()) {
            $this->response->setStatusCode($status);
            $this->response->setContentType($contentType);
            // Echo back the Mcp-Method header for Streamable HTTP compliance
            if ($mcpMethod !== null) {
                $this->response->setHttpHeader('Mcp-Method', $mcpMethod);
            }
            return $responseBody;
        }

        $response = (new Response(status: $status, body: $responseBody))->withAddedHeader("Content-Type", $contentType);

        // Echo back the Mcp-Method header for Streamable HTTP compliance (MCP 2026-07-28)
        if ($mcpMethod !== null) {
            $response = $response->withAddedHeader('Mcp-Method', $mcpMethod);
        }

        return $response;
    }

    /**
     * Extract a header value from the incoming HTTP request.
     */
    protected function extractHttpHeader(string $name): ?string
    {
        $httpRequest = $this->request->getHttpRequest();
        $values = $httpRequest->getHeader($name);
        if (empty($values)) {
            return null;
        }
        return $values[0];
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
