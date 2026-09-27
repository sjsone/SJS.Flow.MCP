<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Protocol;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(enabled: false)]
enum Version: string
{
    case MCP_2026_07_28 = "2026-07-28";
}