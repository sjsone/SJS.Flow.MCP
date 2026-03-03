<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\FeatureSet;

use Neos\Flow\Mvc\ActionRequest;
use SJS\Flow\MCP\Domain\MCP\Tool\Content;
use SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest\Argument;
use SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest\Ref;
use SJS\Flow\MCP\Domain\MCP\Completion;

interface FeatureSetInterface
{

    public function setActionRequest(ActionRequest $request);
    public function initialize();

    /**
     * @param null|string $cursor
     * @return array<\SJS\Flow\MCP\Domain\MCP\Resource>
     */
    public function resourcesList(?string $cursor = null): array;


    /**
     * @return array<\SJS\Flow\MCP\Domain\MCP\Tool>
     */
    public function toolsList(): array;

    public function hasTool(string $toolName): bool;

    /**
     * @param string $toolName
     * @param array $arguments
     * @return void
     */
    public function toolsCall(string $toolName, array $arguments): Content;

    /**
     * @return array<\SJS\Flow\MCP\Domain\MCP\Resource>
     */
    public function resourcesRead(string $uri): array;

    public function resourcesTemplatesList(): array;

    public function completionComplete(Argument $argument, Ref $ref): ?Completion;
}
