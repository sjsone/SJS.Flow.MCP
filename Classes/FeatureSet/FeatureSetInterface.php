<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\FeatureSet;

use SJS\Flow\MCP\Domain\Connection\ServerContext;
use SJS\Flow\MCP\Domain\MCP\Tool\Content;
use SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest\Argument;
use SJS\Flow\MCP\Domain\Client\Request\Completion\CompleteRequest\Ref;
use SJS\Flow\MCP\Domain\MCP\Completion;

interface FeatureSetInterface
{

    public function setServerContext(ServerContext $context): void;

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    public function initialize(): void;

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
     * @param array<string,mixed> $arguments
     */
    public function toolsCall(string $toolName, array $arguments): Content;

    /**
     * @return array<\SJS\Flow\MCP\Domain\MCP\Resource>
     */
    public function resourcesRead(string $uri): array;

    /**
     * @return array<mixed>
     */
    public function resourcesTemplatesList(): array;

    public function completionComplete(Argument $argument, Ref $ref): ?Completion;
}
