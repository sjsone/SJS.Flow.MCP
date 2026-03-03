<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Provider;

use SJS\Flow\MCP\Domain\Model\Agent;

interface AgentProviderInterface
{
    public function initialize(): void;

    public function getAgentByToken(string $token): ?Agent;

}