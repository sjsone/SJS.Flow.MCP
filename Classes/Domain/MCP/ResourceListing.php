<?php

declare(strict_types=1);

namespace SJS\Flow\MCP\Domain\MCP;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class ResourceListing implements \JsonSerializable
{
    /**
     * @param array<Resource> $resources
     * @param null|string $nextCursor
     */
    public function __construct(
        public readonly array $resources,
        public readonly ?string $nextCursor = null,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            "resources" => $this->resources
        ];

        if ($this->nextCursor) {
            $data['nextCursor'] = $this->nextCursor;
        }

        return $data;
    }
}
