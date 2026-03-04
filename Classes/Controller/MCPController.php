<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Controller;

use Neos\Flow\Mvc\Controller\ActionController;
use SJS\Flow\MCP\Domain\Server\Server;
use SJS\Flow\MCP\Domain\Server\ServerFactory;
use Neos\Flow\Annotations as Flow;

class MCPController extends ActionController
{
    #[Flow\Inject()]
    protected ServerFactory $serverFactory;

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

        $server = $this->buildServerFromRequest();
        if ($server === null) {
            throw new \Exception("Could not build server from request");
        }

        $response = $server->handleRequest();

        return $response;
    }

    protected function buildServerFromRequest(): ?Server
    {
        return $this->serverFactory->buildFromName(
            'mcp',
            $this->request
        );
    }
}
