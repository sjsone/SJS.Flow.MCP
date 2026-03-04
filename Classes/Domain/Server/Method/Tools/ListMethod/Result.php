<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\Server\Method\Tools\ListMethod;

use Neos\Flow\Annotations as Flow;
use SJS\Flow\MCP\Domain\MCP\Resource;

#[Flow\Proxy(false)]
class Result implements \JsonSerializable
{
    /**
     * @param array<Resource> $tools
     */
    public function __construct(
        public readonly array $tools,
        public readonly ?string $nextCursor,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'tools' => array_values($this->tools)
        ];

        if ($this->nextCursor) {
            $data['nextCursor'] = $this->nextCursor;
        }
        return $data;
    }
}
